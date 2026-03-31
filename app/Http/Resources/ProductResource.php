<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'internal_id'           => $this->id,
            //'reference'             => $this->external_id, // This line is commented out because the external_id column has been removed from the products table as per the latest migration. If you want to keep it in the future, you can uncomment this line and make sure to add the external_id column back to your products table.
            'salla_ref'             => $this->salla_product_id,
            'name'                  => $this->name,
            'description'           => $this->description,
            'price'                 => $this->price,
            'quantity'              => $this->stock_quantity,
            'image_url' => $this->image_url,
            'created_at'            => $this->created_at?->format('Y-m-d'),
        ];
    }
}
