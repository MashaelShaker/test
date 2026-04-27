<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $event    = $request->input('event');
        $data     = $request->input('data');
        $merchant = (string) $request->input('merchant'); // Salla sends this at the top level

        \Illuminate\Support\Facades\Log::info('Salla webhook received', [
            'event'    => $event,
            'merchant' => $merchant ?: null,
            'data_id'  => is_array($data) ? ($data['id'] ?? null) : null,
        ]);

        if ($event === 'product.updated') {
            (new \App\Actions\Product\Updated($data, $merchant))->handle();
            return response()->json(['success' => true]);
        }

        if ($event === 'product.created') {
            (new \App\Actions\Product\Created($data, $merchant))->handle();
            return response()->json(['success' => true]);
        }

        if ($event === 'product.deleted') {
            (new \App\Actions\Product\Deleted($data))->handle();
            return response()->json(['success' => true]);
        }

        // SKU-level events — Salla fires these when per-variant stock changes.
        // The event's data is the VARIANT, not the product, so pull the parent
        // product id out and re-fetch it so we pick up fresh SKU numbers.
        if (in_array($event, ['product.variant.updated', 'product.variant.created', 'product.variant.deleted'], true)) {
            $parentId = $data['product_id']
                ?? $data['product']['id']
                ?? $data['parent_product_id']
                ?? $data['parent_id']
                ?? null;

            if (!$parentId) {
                \Illuminate\Support\Facades\Log::warning('variant webhook: no parent id in payload', [
                    'event'     => $event,
                    'data_keys' => is_array($data) ? array_keys($data) : null,
                    'data'      => $data,
                ]);
            } else {
                \Illuminate\Support\Facades\Log::info('variant webhook → resync parent', [
                    'event'     => $event,
                    'parent_id' => $parentId,
                    'merchant'  => $merchant ?: null,
                ]);
                (new \App\Actions\Product\Updated(['id' => $parentId], $merchant))->handle();
            }
            return response()->json(['success' => true]);
        }

        if ($event === 'app.store.authorize') {
            (new \App\Actions\App\StoreAuthorize($data))->handle();
            return response()->json(['success' => true]);
        }

    if ($event === 'app.installed') {
        $merchant = $request->input('merchant') ?? data_get($data, 'merchant');
        (new \App\Actions\App\Installed())->handle($merchant);
        return response()->json(['success' => true]);
    }

        return response()->json(['success' => true]);
    }
}
