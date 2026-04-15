<?php

namespace App\Actions\Product;

use App\Actions\BaseAction;
use App\Models\Product;

/**
 * @property string merchant example "1029864349"
 * @property string created_at example "Wed Jun 30 2021 12:16:25 GMT+030"
 * @property string event example "product.deleted"
 * @property array data @see
 *     https://docs.salla.dev/docs/merchent/openapi.json/components/schemas/ProductsWebhookResponse
 */
class Deleted extends BaseAction
{
public function handle()
{
    
    Product::where('id', $this->data['id'])->delete();
}
}
