<?php

namespace App\Services;

use App\Models\Box;
use App\Models\OauthToken;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Shared product-from-Salla sync logic used by both the scheduled
 * app:sync-products command and the product.* webhook actions.
 */
class ProductSyncService
{
    /**
     * Fetch a single product from Salla by ID and upsert it locally.
     * Used by webhooks so a product edit in Salla reflects within seconds
     * without waiting for the scheduled sync.
     */
    public function fetchAndSyncByMerchant(string $merchant, int $sallaProductId): ?Product
    {
        $token = OauthToken::where('merchant', $merchant)->first();
        $user  = $token ? User::find($token->user_id) : null;
        if (!$user) {
            Log::warning('ProductSyncService: no user/token for merchant', ['merchant' => $merchant]);
            return null;
        }

        return $this->fetchAndSync($user, $sallaProductId);
    }

    public function fetchAndSync(User $user, int $sallaProductId): ?Product
    {
        try {
            $accessToken = app(SallaAuthService::class)->forUser($user)->freshAccessToken();
        } catch (\Throwable $e) {
            Log::error('ProductSyncService: token refresh failed', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return null;
        }

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get("https://api.salla.dev/admin/v2/products/{$sallaProductId}?include=variants,options");

        if (!$response->successful()) {
            Log::warning('ProductSyncService: Salla fetch failed', [
                'product_id' => $sallaProductId,
                'status'     => $response->status(),
            ]);
            return null;
        }

        $item = $response->json('data');
        if (!is_array($item)) {
            return null;
        }

        $storeId = $user->store_id ?? optional($user->token)->merchant;

        return $this->upsertFromSallaItem($item, $storeId);
    }

    /**
     * Upsert a Product row from a raw Salla product payload.
     * Returns null when the product is already registered as a Box (we don't
     * want boxes to appear as their own source products).
     */
    public function upsertFromSallaItem(array $item, $storeId): ?Product
    {
        if (!isset($item['id'])) {
            return null;
        }

        if (Box::where('salla_product_id', $item['id'])->exists()) {
            return null;
        }

        return Product::updateOrCreate(
            ['salla_product_id' => $item['id']],
            [
                'name'           => $item['name'] ?? '',
                'description'    => $item['description'] ?? '',
                'price'          => $item['price']['amount'] ?? 0,
                'stock_quantity' => $item['quantity'] ?? 0,
                'image_url'      => $item['main_image'] ?? '',
                'variants_data'  => $this->computeVariantsData($item),
                'store_id'       => $storeId,
            ]
        );
    }

    /**
     * Produce the per-variant stock rows we store in products.variants_data.
     * Full rule tree — see comments inline.
     */
    public function computeVariantsData(array $item): array
    {
        $productQty       = (int) ($item['quantity'] ?? 0);
        $productUnlimited = !empty($item['unlimited_quantity']);

        // Own option value IDs in the order Salla defines them (order matters —
        // used as a positional fallback for SKUs whose related_option_values are
        // orphaned/foreign, as observed in the Salla sandbox).
        $ownOptionValuesOrdered = [];
        foreach ($item['options'] ?? [] as $option) {
            foreach ($option['values'] ?? [] as $value) {
                $ownOptionValuesOrdered[] = $value['id'];
            }
        }
        $ownOptionIds = array_flip($ownOptionValuesOrdered);

        // Pass 1: match each SKU to own options via related_option_values
        // (authoritative when refs are clean). MAX not SUM avoids double-counting
        // a unit that spans two option dimensions.
        $stockByOptionValue     = [];
        $unlimitedByOptionValue = [];
        $matchedOwnIds          = [];
        $unmatchedSkus          = [];
        foreach ($item['skus'] ?? [] as $sku) {
            $skuStock     = (int) ($sku['stock_quantity'] ?? 0);
            $skuUnlimited = !empty($sku['unlimited_quantity']);
            $matched      = false;
            foreach ($sku['related_option_values'] ?? [] as $valueId) {
                if (!isset($ownOptionIds[$valueId])) {
                    continue;
                }
                $stockByOptionValue[$valueId] = max(
                    $stockByOptionValue[$valueId] ?? 0,
                    $skuStock
                );
                if ($skuUnlimited) {
                    $unlimitedByOptionValue[$valueId] = true;
                }
                $matchedOwnIds[$valueId] = true;
                $matched = true;
            }
            if (!$matched) {
                $unmatchedSkus[] = $sku;
            }
        }

        // Pass 2: positionally assign orphaned SKUs to still-unmatched own
        // option values — ONLY when counts line up exactly. Mismatched counts
        // mean extra SKUs belong to other products and assigning would poison
        // stock with arbitrary numbers.
        $unmatchedOwnOrdered = array_values(array_filter(
            $ownOptionValuesOrdered,
            fn ($id) => !isset($matchedOwnIds[$id])
        ));
        if (count($unmatchedOwnOrdered) === count($unmatchedSkus)) {
            foreach ($unmatchedSkus as $i => $sku) {
                $valueId = $unmatchedOwnOrdered[$i];
                $stockByOptionValue[$valueId] = (int) ($sku['stock_quantity'] ?? 0);
                if (!empty($sku['unlimited_quantity'])) {
                    $unlimitedByOptionValue[$valueId] = true;
                }
            }
        }

        $syncedData = [];

        // Legacy variants shape (rare; some Salla products still expose it)
        if (!empty($item['variants'])) {
            foreach ($item['variants'] as $variant) {
                $hasVariantStock = isset($variant['stock_quantity']) || isset($variant['quantity']);
                $syncedData[] = [
                    'id'                 => $variant['id'],
                    'name'               => $variant['name'],
                    'image'              => $variant['image']['url'] ?? $item['main_image'] ?? null,
                    'quantity'           => $hasVariantStock
                        ? (int) ($variant['stock_quantity'] ?? $variant['quantity'] ?? 0)
                        : $productQty,
                    'unlimited_quantity' => $hasVariantStock
                        ? !empty($variant['unlimited_quantity'])
                        : $productUnlimited,
                ];
            }
            return $syncedData;
        }

        // Options shape. Per-value fallback:
        //   - If any SKU joined → un-joined values are 0 (seller didn't track them)
        //   - If no SKU joined at all → fall back to product-level stock so
        //     every size is visible while the product has total stock.
        // Each entry also carries option_name so the storefront widget can group
        // values by their parent option (e.g. render Color then Size as two steps).
        if (!empty($item['options'])) {
            $anySkuJoined = !empty($stockByOptionValue);
            foreach ($item['options'] as $option) {
                $optionName = $option['name'] ?? null;
                if (isset($option['values']) && is_array($option['values'])) {
                    foreach ($option['values'] as $value) {
                        $hasValueStock = array_key_exists($value['id'], $stockByOptionValue);
                        $syncedData[] = [
                            'id'                 => $value['id'],
                            'name'               => $value['name'],
                            'option_name'        => $optionName,
                            'image'              => $value['image'] ?? $item['main_image'] ?? null,
                            'quantity'           => $hasValueStock
                                ? (int) $stockByOptionValue[$value['id']]
                                : ($anySkuJoined ? 0 : $productQty),
                            'unlimited_quantity' => $hasValueStock
                                ? !empty($unlimitedByOptionValue[$value['id']])
                                : (!$anySkuJoined && $productUnlimited),
                        ];
                    }
                }
            }
        }

        return $syncedData;
    }
}
