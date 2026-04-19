<?php

namespace App\Models;

use App\Models\Scopes\CurrentStoreScope;
use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    protected $fillable = [
        'store_id',
        'name',
        'price',
        'description',
        'image_url',
        'salla_product_id' // ✅ لازم تنضاف
    ];

    protected static function booted()
    {
        static::addGlobalScope(new CurrentStoreScope());

        static::creating(function ($box) {
            if (auth()->check()) {
                $user = auth()->user();
                $box->store_id = $user->store_id ?? $user->token->merchant;
            }
        });
    }

    public function elements()
    {
        return $this->hasMany(BoxElement::class);}
 public function getImageUrlAttribute($value)
{
    // If there is no image in the database, return null so Blade uses the fallback
    if (!$value) {
        return null;
    }

    // If it's already a full URL (like a placeholder), just return it
    if (filter_var($value, FILTER_VALIDATE_URL)) {
        return $value;
    }

    // Otherwise, wrap it in url()
    return url($value);
}
}
