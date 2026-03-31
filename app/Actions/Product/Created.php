<?php

namespace App\Actions\Product;

use App\Actions\BaseAction;

/**
 * @property string merchant example "1029864349"
 * @property string created_at example "Wed Jun 30 2021 12:16:25 GMT+030"
 * @property string event example "product.created"
 * @property array data @see
 *     https://docs.salla.dev/docs/merchent/openapi.json/components/schemas/ProductsWebhookResponse
 */
use App\Models\Product;

class Created extends BaseAction
{
    public function handle()
    {
        //use same logic you have in app/Actions/Product/Updated.php
        return Product::updateOrCreate(
            ['salla_product_id' => $this->data['id']],
            [
                'external_id'    => $this->data['sku'] ?? null,
                'name'           => $this->data['name'] ?? '',
                'description'    => $this->data['description'] ?? '',
                'price'          => $this->data['price']['amount'] ?? 0,
                'stock_quantity' => $this->data['quantity'] ?? 0,
                'image_url'      => $this->data['main_image'] ?? null,
            ]
        );
    }
}
