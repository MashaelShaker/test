<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncStoreId extends Command
{
    protected $signature = 'sync:store-id';
    protected $description = 'Fetch store ID from Salla and update all products';

    public function handle()
    {
        $response = Http::withToken(env('SALLA_API_KEY'))
            ->get('https://api.salla.dev/admin/v2/store/info');

        if (!$response->successful()) {
            $this->error('Failed to fetch store info');
            return;
        }

        $storeId = $response->json('data.id');

        Product::whereNull('store_id')->update(['store_id' => $storeId]);

        $this->info("Store ID {$storeId} applied to all products.");
    }
}