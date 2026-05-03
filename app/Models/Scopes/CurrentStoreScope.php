<?php


namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CurrentStoreScope implements Scope
{
   public function apply(Builder $builder, Model $model): void
{
    if (!auth()->check()) {
        return;
    }
    $builder->where('store_id', auth()->user()->store_id);
}
}
