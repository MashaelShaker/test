<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Actions\Product\Updated;

class WebhookController extends Controller
{
   public function handle(Request $request)
{
    $event = $request->input('event');
    $data  = $request->input('data');

    if ($event === 'product.updated') {
        (new \App\Actions\Product\Updated($data))->handle();
        return response()->json(['success' => true]);
    }

    if ($event === 'product.created') {
        (new \App\Actions\Product\Created($data))->handle();
        return response()->json(['success' => true]);
    }

    if ($event === 'product.deleted') {
        (new \App\Actions\Product\Deleted($data))->handle();
        return response()->json(['success' => true]);
    }

    if ($event === 'app.store.authorize') {
        (new \App\Actions\App\StoreAuthorize($data))
          //  ->setRequest(request())
            ->handle();
        return response()->json(['success' => true]);
    }

    return response()->json(['success' => true]);
}
}
