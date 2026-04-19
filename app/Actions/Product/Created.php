<?php

namespace App\Actions\Product;

use App\Actions\BaseAction;
use App\Models\Box;
use App\Models\Product;

class Created extends BaseAction
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        if (Box::where('salla_product_id', $this->data['id'])->exists()) {
            return null;
        }

        return Product::updateOrCreate(
            ['salla_product_id' => $this->data['id']],
            [
                'name'           => $this->data['name'] ?? '',
                'description'    => $this->data['description'] ?? '',
                'price'          => $this->data['price']['amount'] ?? 0,
                'stock_quantity' => $this->data['quantity'] ?? 0,
                'image_url'      => $this->data['main_image'] ?? '',
            ]
        );
    }
}
