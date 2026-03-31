<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    // 1. Specify the table name
    protected $table = 'products';

    // Add these two lines to prevent 500 errors during saving
    public $incrementing = true; // false; The migration defines 'id' as an auto-incrementing integer, so this should be true.
    protected $keyType = 'int'; // 'string'; Since 'id' is an integer, we set the key type to 'int'.
    // 2. Allow these fields to be filled by your Sync command
    protected $fillable = [
        'salla_product_id',
        //'external_id', // Removed as per the latest migration that drops this column, Just in case you want to keep it in the future, you can uncomment this line.
        'name',
        'description',
        'price',
        'stock_quantity',
        'image_url'
    ];
}
