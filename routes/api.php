<?php
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoxController;

Route::post('/webhook', [WebhookController::class, 'handle']);
Route::match(['GET', 'OPTIONS'], '/public/boxes/{salla_product_id}', [BoxController::class, 'details']);

