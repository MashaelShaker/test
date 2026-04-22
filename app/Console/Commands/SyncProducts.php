<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ProductSyncService;
use App\Services\SallaAuthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
    public function handle(ProductSyncService $sync)
    {
        $this->info("Starting sync...");
        $totalSynced = 0;

        // all products from every authorized store should be synced
        $users = User::whereHas('token')->get();

        foreach ($users as $user) {
            $store_id = $user->store_id ?? $user->token->merchant;

            try {
                $token = app(SallaAuthService::class)->forUser($user)->freshAccessToken();
            } catch (\Throwable $e) {
                Log::error('SyncProducts: token refresh failed — store must reauthorize', [
                    'store_id' => $store_id,
                    'message'  => $e->getMessage(),
                ]);
                $this->warn("Skipping store $store_id — token refresh failed (needs reauthorization)");
                continue;
            }

            $nextPageUrl = "https://api.salla.dev/admin/v2/products?per_page=100&include=variants,options";

            while ($nextPageUrl) {
                $response = Http::withToken($token)->get($nextPageUrl);

                if ($response->successful()) {
                    $result = $response->json();

                    foreach ($result['data'] as $item) {
                        if ($sync->upsertFromSallaItem($item, $store_id) !== null) {
                            $totalSynced++;
                        }
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
