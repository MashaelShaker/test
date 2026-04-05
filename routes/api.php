<?php
// routes/api.php
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BoxController;

Route::get('/boxes', [BoxController::class, 'index']);
Route::get('/boxes/{id}', [BoxController::class, 'show']);
Route::post('/boxes', [BoxController::class, 'store']);

Route::post('/webhook', [WebhookController::class, 'handle']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

