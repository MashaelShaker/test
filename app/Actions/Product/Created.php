<?php

namespace App\Actions\Product;

use App\Actions\BaseAction;
use App\Models\Box;
use App\Services\ProductSyncService;
use Illuminate\Support\Facades\Log;

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
        $sallaProductId = $this->data['id'] ?? null;
        if (!$sallaProductId) {
            return null;
        }

        if (Box::where('salla_product_id', $sallaProductId)->exists()) {
            return null;
        }

        if (!$this->merchant) {
            Log::warning('Product\Created: missing merchant in webhook; cannot fetch fresh data', [
                'salla_product_id' => $sallaProductId,
            ]);
            return null;
        }

        return app(ProductSyncService::class)
            ->fetchAndSyncByMerchant($this->merchant, (int) $sallaProductId);
    }
}
