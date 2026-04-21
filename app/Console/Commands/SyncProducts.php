<?php

namespace App\Console\Commands;

use App\Models\Box;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Product;

class SyncProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch products from Salla API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting sync...");
        $totalSynced = 0;

        // we nned to make sure after the app is instlled by 2 stores . all products from both stores should be synced
        $users = User::whereHas('token')->get();

        foreach ($users as $user) {
            $store_id = $user->store_id ?? $user->token->merchant;
            $token = $user->token->access_token;
            $nextPageUrl = "https://api.salla.dev/admin/v2/products?per_page=100include=variants,options";

            while ($nextPageUrl) {
                $response = Http::withToken($token)->get($nextPageUrl);

                if ($response->successful()) {
                    $result = $response->json();

                    foreach ($result['data'] as $item) {
                        if (Box::where('salla_product_id', $item['id'])->exists()) {
                            continue;
                        }

                        $syncedData = [];
                        // 1. Check for Variants first
                        if (!empty($item['variants'])) {
                            foreach ($item['variants'] as $variant) {
                                $syncedData[] = [
                                    'id'    => $variant['id'],
                                    'name'  => $variant['name'],
                                    'image' => $variant['image']['url'] ?? $item['main_image'] ?? null,
                                ];
                            }
                        }
                        // 2. IMPORTANT: If variants are empty, pull from Options
                        elseif (!empty($item['options'])) {
                            foreach ($item['options'] as $option) {
                                // We look inside 'values' for the actual choices (36, 38, 40...)
                                if (isset($option['values']) && is_array($option['values'])) {
                                    foreach ($option['values'] as $value) {
                                        $syncedData[] = [
                                            'id'    => $value['id'],
                                            'name'  => $value['name'],
                                            'image' => $value['image'] ?? $item['main_image'] ?? null,
                                        ];
                                    }
                                }
                            }
                        }

                        Product::updateOrCreate(
                            ['salla_product_id' => $item['id']],
                            [
                                'name'           => $item['name'],
                                'description'    => $item['description'] ?? '',
                                'price'          => $item['price']['amount'] ?? 0,
                                'stock_quantity' => $item['quantity'] ?? 0,
                                'image_url'      => $item['main_image'] ?? '',
                                'variants_data'  => $syncedData,
                                'store_id'       => $store_id,
                            ]
                        );


                        $totalSynced++;
                    }
                    // Move to the next page if it exists
                    $nextPageUrl = $result['pagination']['links']['next'] ?? null;
                    if ($nextPageUrl && !str_contains($nextPageUrl, 'include=variants')) {
                    $nextPageUrl .= (str_contains($nextPageUrl, '?') ? '&' : '?') . 'include=variants';
                }

                } else {
                    $this->error("Error in fetching from store $store_id: " . $response->body());
                    break;
                }
            }
        }

        $this->info("Done! Synced $totalSynced products successfully.");
    }
}
