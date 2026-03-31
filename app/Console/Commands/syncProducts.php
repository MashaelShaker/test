<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Product;

class syncProducts extends Command
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
        // Get token from .env
        /*
         * here we need to loop throw all our registered users you can get the apiKey for each user from  $user->token->access_token
         * also make sure you add store_id in products table where products.store_id=$user->token->merchant
         */ 
        $token = env('SALLA_API_KEY');
        $totalSynced = 0;

        $nextPageUrl = "https://api.salla.dev/admin/v2/products?per_page=100";

        while ($nextPageUrl) {
            $response = Http::withToken($token)->get($nextPageUrl);

            if ($response->successful()) {
                $result = $response->json();

                foreach ($result['data'] as $item) {
                    $product = Product::updateOrCreate(
                        ['salla_product_id' => $item['id']],
                        [
                            'name'           => $item['name'],
                            'description'    => $item['description'] ?? '',
                            'price'          => $item['price']['amount'] ?? 0,
                            'stock_quantity' => $item['quantity'] ?? 0,
                            'image_url'      => $item['main_image'] ?? '',
                        ]
                    );
                    // $product->external_id = 'PRDO-' . $product->id . '-SALLA-' . $item['id']; // This line is commented out because the external_id column has been removed from the products table as per the latest migration. If you want to keep it in the future, you can uncomment this line and make sure to add the external_id column back to your products table.
                    // $product->save();

                    $totalSynced++;
                }
                // Move to the next page if it exists
                $nextPageUrl = $result['pagination']['links']['next'] ?? null;
            } else {
                $this->error("Error in fetching: " . $response->body());
                break;
            }
        }

        $this->info("Done! Synced $totalSynced products successfully.");
    }
}
