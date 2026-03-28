<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'internal_id'           => $this->id,
            'reference'             => $this->external_id,
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
