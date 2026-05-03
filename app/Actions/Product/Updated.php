<?php
namespace App\Actions\Product;

use App\Actions\BaseAction;
use App\Models\Box;
use App\Models\Product;

class Updated extends BaseAction
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        $sallaProductId = $this->data['id'];

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

        return Product::updateOrCreate(
            ['salla_product_id' => $sallaProductId],
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
