<?php

namespace App\Models;

use App\Models\Scopes\CurrentStoreScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new CurrentStoreScope());
    }

    protected $table = 'products';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'store_id',
        'salla_product_id',
        'name',
        'description',
        'price',
        'stock_quantity',
        'image_url',
        'variants_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'variants_data' => 'array',
        'salla_product_id' => 'integer',
        'price' => 'decimal:2',
    ];

    public function boxElements()
    {
        return $this->belongsToMany(BoxElement::class, 'box_element_product', 'product_id', 'box_element_id');
    }
}
