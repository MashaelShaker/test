<?php

namespace App\Actions\Product;

use App\Actions\BaseAction;
use App\Models\Box;
use App\Services\ProductSyncService;
use Illuminate\Support\Facades\Log;

class Updated extends BaseAction
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
        $sallaProductId = $this->data['id'] ?? null;
        if (!$sallaProductId) {
            return null;
        }

        // Boxes are our own generated products — update just the shallow fields.
        $box = Box::where('salla_product_id', $sallaProductId)->first();
        if ($box) {
            $box->update([
                'name'        => $this->data['name'] ?? $box->name,
                'description' => $this->data['description'] ?? $box->description,
                'price'       => $this->data['price']['amount'] ?? $box->price,
                'image_url'   => $this->data['main_image'] ?? $box->image_url,
            ]);

            return $box;
        }

        // Regular catalog product — re-fetch from Salla to pick up fresh
        // options/SKUs, then run the same logic as the scheduled sync.
        if (!$this->merchant) {
            Log::warning('Product\Updated: missing merchant in webhook; cannot refresh stock', [
                'salla_product_id' => $sallaProductId,
            ]);
            return null;
        }

        return app(ProductSyncService::class)
            ->fetchAndSyncByMerchant($this->merchant, (int) $sallaProductId);
    }
}
