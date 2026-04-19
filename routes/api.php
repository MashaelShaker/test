<?php
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoxController;

Route::post('/webhook', [WebhookController::class, 'handle']);
Route::options('/public/boxes/{salla_product_id}', function () {
	return response()->noContent()
		->header('Access-Control-Allow-Origin', '*')
		->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
		->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, ngrok-skip-browser-warning');
});
Route::get('/public/boxes/{salla_product_id}', [BoxController::class, 'details']);

