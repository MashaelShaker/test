<?php
// routes/api.php
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/webhook', [WebhookController::class, 'handle']);
Route::get('/products', [ProductController::class, 'index']); 