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
    protected $signature = 'app:sync-products {--merchant= : Sync only this store merchant id}';

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
        $merchant = $this->option('merchant');

        // we nned to make sure after the app is instlled by 2 stores . all products from both stores should be synced
        $usersQuery = User::whereHas('token');

        if (!empty($merchant)) {
            $usersQuery->where(function ($query) use ($merchant) {
                $query->where('store_id', $merchant)
                    ->orWhereHas('token', function ($tokenQuery) use ($merchant) {
                        $tokenQuery->where('merchant', $merchant);
                    });
            });
        }

        $users = $usersQuery->get();


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
