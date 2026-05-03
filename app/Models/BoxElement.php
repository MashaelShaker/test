<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoxElement extends Model
{
    protected $fillable = ['box_id', 'element_name'];

    public function box()
    {
        return $this->belongsTo(Box::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'box_element_product', 'box_element_id', 'product_id');
    }
}