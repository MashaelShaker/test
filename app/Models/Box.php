<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    protected $fillable = ['store_id', 'name', 'price', 'description', 'image_url'];

    public function elements()
    {
        return $this->hasMany(BoxElement::class);
    }
}