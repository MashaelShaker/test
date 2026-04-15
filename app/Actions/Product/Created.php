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
    /*if (\App\Models\Box::where('salla_product_id', $this->data['id'])->exists()) {
        return null;
    }*/
        

    $product = Product::updateOrCreate(
        ['salla_product_id' => $this->data['id']],
        [
            'name'           => $this->data['name'] ?? '',
            'description'    => $this->data['description'] ?? '',
            'price'          => $this->data['price']['amount'] ?? 0,
            'stock_quantity' => $this->data['quantity'] ?? 0,
            'image_url'      => $this->data['main_image'] ?? '',
        ]
    );

    $product->external_id = 'PRDO-' . $product->id . '-SALLA-' . $this->data['id'];
    $product->save();

    return $product;
}
}
