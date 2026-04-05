<?php

namespace App\Console\Commands;

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
            $store_id = $user->token->merchant;
            $token = $user->token->access_token;
            $nextPageUrl = "https://api.salla.dev/admin/v2/products?per_page=100";

            while ($nextPageUrl) {
                $response = Http::withToken($token)->get($nextPageUrl);

                if ($response->successful()) {
                    $result = $response->json();

                    foreach ($result['data'] as $item) {
                        Product::updateOrCreate(
                            ['salla_product_id' => $item['id']],
                            [
                                'name'           => $item['name'],
                                'description'    => $item['description'] ?? '',
                                'price'          => $item['price']['amount'] ?? 0,
                                'stock_quantity' => $item['quantity'] ?? 0,
                                'image_url'      => $item['main_image'] ?? '',
                                'store_id'       => $store_id,
                            ]
                        );


                        $totalSynced++;
                    }
                    // Move to the next page if it exists
                    $nextPageUrl = $result['pagination']['links']['next'] ?? null;
                } else {
                    $this->error("Error in fetching: " . $response->body());
                    break;
                }
            }
        }

        $this->info("Done! Synced $totalSynced products successfully.");
    }
}
