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
        'image_id',
        'salla_product_id',
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
        return $this->hasMany(BoxElement::class);
    }
    public function getImageUrlAttribute($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $base = rtrim((string) config('app.url'), '/');

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            $host = parse_url($value, PHP_URL_HOST);
            if (in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]'], true)) {
                $path = parse_url($value, PHP_URL_PATH) ?? '';

                return $base . $path;
            }

            return $value;
        }

        return $base . '/' . ltrim($value, '/');
    }

}
