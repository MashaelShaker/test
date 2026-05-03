<?php

namespace App\Actions\Order;

use App\Actions\BaseAction;
use App\Models\Box;
use App\Models\OauthToken;
use App\Models\Product;
use App\Models\User;
use App\Services\SallaAuthService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * When a Salla order is created, decrement source-product stock for each
 * box line item. The storefront snippet embeds the picks in the cart-item
 * note inside a [BOX_DATA]...[/BOX_DATA] marker; Salla preserves notes
 * onto order items, so we parse them here to know which source SKUs to
 * decrement. Notes are read-only to the customer in Salla's cart UI, and
 * deleting the note removes the line entirely — so the marker is safe.
 *
 * @property string merchant
 * @property string created_at
 * @property string event
 * @property array  data  @see https://docs.salla.dev/docs/merchent/openapi.json/components/schemas/OrdersWebhookResponse
 */
class Created extends BaseAction
{
    protected array $data;
    protected ?string $merchant;

    public function __construct(array $data, ?string $merchant = null)
    {
        $this->data     = $data;
        $this->merchant = $merchant;
    }

    public function handle()
    {
        $orderId = $this->data['id'] ?? null;
        $items   = $this->data['items'] ?? [];

        if (!is_array($items) || empty($items)) {
            Log::info('order.created: no items', ['order_id' => $orderId]);
            return;
        }

        $token = $this->resolveToken();

        foreach ($items as $item) {
            $sallaProductId = $item['product_id'] ?? $item['product']['id'] ?? null;
            $quantity       = (int) ($item['quantity'] ?? 1);
            $notes          = $item['notes'] ?? null;

            if (!$sallaProductId) continue;

            $box = Box::where('salla_product_id', $sallaProductId)->first();
            if (!$box) continue;

            $picks = $this->parseBoxData($notes);

            if (empty($picks)) {
                Log::warning('order.created: box item missing BOX_DATA block', [
                    'order_id'         => $orderId,
                    'box_id'           => $box->id,
                    'salla_product_id' => $sallaProductId,
                ]);
                continue;
            }

            foreach ($picks as $pick) {
                $this->decrementOnePick($pick, $quantity, $token);
            }
        }
    }

    /**
     * Pull the picks out of an order item's notes. The storefront snippet writes
     * a [BOX_DATA]...[/BOX_DATA] block where the body is pipe-separated picks
     * shaped as `productId:variantId,variantId|productId:variantId|...`.
     * Returns [] when the marker is missing or malformed — caller logs and skips.
     */
    private function parseBoxData(?string $notes): array
    {
        if (!is_string($notes) || $notes === '') return [];

        if (!preg_match('/\[BOX_DATA\](.+?)\[\/BOX_DATA\]/s', $notes, $m)) {
            return [];
        }

        $payload = trim($m[1]);
        if ($payload === '') return [];

        $picks = [];
        foreach (explode('|', $payload) as $segment) {
            $segment = trim($segment);
            if ($segment === '' || !str_contains($segment, ':')) continue;

            [$productId, $variantsCsv] = explode(':', $segment, 2);
            $productId = (int) trim($productId);
            if ($productId <= 0) continue;

            $variantIds = array_values(array_filter(array_map(
                fn($v) => (int) trim($v),
                $variantsCsv === '' ? [] : explode(',', $variantsCsv)
            )));

            $picks[] = [
                'source_product_id' => $productId,
                'variant_value_ids' => $variantIds,
            ];
        }

        return $picks;
    }

    /**
     * Returns the merchant's fresh access token for Salla admin calls, or null
     * when we can't resolve a user — we still update local stock in that case.
     */
    private function resolveToken(): ?string
    {
        if (!$this->merchant) return null;

        $oauth = OauthToken::where('merchant', $this->merchant)->first();
        $user  = $oauth ? User::find($oauth->user_id) : null;
        if (!$user) return null;

        try {
            return app(SallaAuthService::class)->forUser($user)->freshAccessToken();
        } catch (\Throwable $e) {
            Log::warning('order.created: token refresh failed', [
                'merchant' => $this->merchant,
                'message'  => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Decrement one element's worth of stock for a single picked source product
     * by `$units` (= the box line item's order quantity). Updates our local
     * Product row first; mirrors to Salla best-effort if we have a token.
     */
    private function decrementOnePick(array $pick, int $units, ?string $token): void
    {
        $localProductId = (int) ($pick['source_product_id'] ?? 0);
        $variantIds     = array_map('intval', $pick['variant_value_ids'] ?? []);
        $units          = max(1, $units);

        $product = Product::find($localProductId);
        if (!$product) {
            Log::warning('order.created: source product not found locally', [
                'source_product_id' => $localProductId,
            ]);
            return;
        }

        $this->decrementLocal($product, $variantIds, $units);

        if ($token && $product->salla_product_id) {
            $this->decrementOnSalla($token, $product, $variantIds, $units);
        }
    }

    /**
     * Adjust the local Product row in place. For products with combinations,
     * find the matching combo (set-equal on option_value_ids) and reduce its
     * quantity. For single-dim values-only products, reduce the matching value.
     * Falls back to top-level stock_quantity when nothing else matches.
     */
    private function decrementLocal(Product $product, array $variantIds, int $units): void
    {
        $variantsData = is_array($product->variants_data) ? $product->variants_data : [];
        $combos       = $variantsData['combinations'] ?? [];
        $values       = $variantsData['values'] ?? [];
        $matched      = false;

        if (!empty($combos)) {
            foreach ($combos as &$combo) {
                $comboIds = array_map('intval', $combo['option_value_ids'] ?? []);
                if ($this->sameIdSet($comboIds, $variantIds)) {
                    $combo['quantity'] = max(0, ((int) ($combo['quantity'] ?? 0)) - $units);
                    $matched = true;
                    break;
                }
            }
            unset($combo);
        }

        if (!$matched && !empty($values) && count($variantIds) === 1) {
            foreach ($values as &$value) {
                if ((int) ($value['id'] ?? 0) === $variantIds[0]) {
                    $value['quantity'] = max(0, ((int) ($value['quantity'] ?? 0)) - $units);
                    $matched = true;
                    break;
                }
            }
            unset($value);
        }

        if ($matched) {
            $variantsData['values']       = $values;
            $variantsData['combinations'] = $combos;
            $product->variants_data = $variantsData;
        } else {
            // No variant match — degrade to product-level stock so the count
            // doesn't drift on the rare plain-product case.
            $product->stock_quantity = max(0, ((int) $product->stock_quantity) - $units);
        }

        $product->save();

        Log::info('order.created: local stock decremented', [
            'source_product_id' => $product->id,
            'variant_value_ids' => $variantIds,
            'units'             => $units,
            'matched_variant'   => $matched,
        ]);
    }

    /**
     * Decrement on Salla via the bulk quantities endpoint with mode=decrement —
     * Salla does the arithmetic so we don't have to read-modify-write. Looks up
     * the matching SKU first because variant_id in this API is the SKU id
     * Salla returns under `data.skus[].id`. Failures are logged, never thrown:
     * local DB stays the source of truth.
     *
     * Multi-dim products match by ID set (Salla's `skus[].related_option_values`
     * lines up with the picked option-value IDs). Single-dim products in some
     * stores have SKUs that reference a different ID namespace from the option
     * values — for those we fall back to position-based matching, which assumes
     * `data.options[*].values[i]` aligns with `data.skus[i]`.
     *
     * Note: Salla's field name is misspelled `identifer` (not `identifier`) —
     * we send it as documented or the request fails.
     */
    private function decrementOnSalla(string $token, Product $product, array $variantIds, int $units): void
    {
        try {
            $res = Http::withToken($token)->acceptJson()
                ->get("https://api.salla.dev/admin/v2/products/{$product->salla_product_id}");

            if (!$res->successful()) {
                Log::warning('order.created: Salla product GET failed', [
                    'salla_product_id' => $product->salla_product_id,
                    'status'           => $res->status(),
                ]);
                return;
            }

            $skus = $res->json('data.skus', []) ?: [];
            $matchedSku = null;
            $matchStrategy = null;

            foreach ($skus as $sku) {
                $skuValueIds = array_map('intval', $sku['related_option_values'] ?? []);
                if ($this->sameIdSet($skuValueIds, $variantIds)) {
                    $matchedSku = $sku;
                    $matchStrategy = 'id_set';
                    break;
                }
            }

            if (!$matchedSku && count($variantIds) === 1) {
                $options  = $res->json('data.options', []) ?: [];
                $pickedId = $variantIds[0];
                $index    = null;

                foreach ($options as $option) {
                    foreach (($option['values'] ?? []) as $i => $value) {
                        if ((int) ($value['id'] ?? 0) === $pickedId) {
                            $index = $i;
                            break 2;
                        }
                    }
                }

                if ($index !== null && isset($skus[$index])) {
                    $matchedSku    = $skus[$index];
                    $matchStrategy = 'index_fallback';
                }
            }

            if (!$matchedSku || empty($matchedSku['id'])) {
                Log::info('order.created: no matching Salla SKU for pick — skipping remote decrement', [
                    'salla_product_id'  => $product->salla_product_id,
                    'variant_value_ids' => $variantIds,
                ]);
                return;
            }

            $payload = [
                'products' => [[
                    'identifer_type' => 'variant_id',
                    'identifer'      => (string) $matchedSku['id'],
                    'quantity'       => $units,
                    'mode'           => 'decrement',
                ]],
            ];

            $resp = Http::withToken($token)->acceptJson()
                ->post('https://api.salla.dev/admin/v2/products/quantities/bulk', $payload);

            Log::info('order.created: Salla SKU quantity decrement', [
                'salla_product_id' => $product->salla_product_id,
                'sku_id'           => $matchedSku['id'],
                'units'            => $units,
                'match_strategy'   => $matchStrategy,
                'status'           => $resp->status(),
                'success'          => $resp->successful(),
                'body'             => $resp->successful() ? null : $resp->body(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('order.created: Salla decrement threw', [
                'salla_product_id' => $product->salla_product_id,
                'message'          => $e->getMessage(),
            ]);
        }
    }

    private function sameIdSet(array $a, array $b): bool
    {
        if (count($a) !== count($b)) return false;
        sort($a);
        sort($b);
        return $a === $b;
    }
}