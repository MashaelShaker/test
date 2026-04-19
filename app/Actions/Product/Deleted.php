<?php

namespace App\Actions\Product;

use App\Actions\BaseAction;
use App\Models\Box;
use App\Models\Product;

class Deleted extends BaseAction
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
            return $box->delete();
        }

        return Product::where('salla_product_id', $sallaProductId)->delete();
    }
}
