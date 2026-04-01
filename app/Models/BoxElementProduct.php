<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoxElementProduct extends Model
{
    public $timestamps = false;
    protected $fillable = ['box_element_id', 'product_id'];
}