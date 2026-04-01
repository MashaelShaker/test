<?php
// routes/api.php
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoxController;


Route::post('/webhook', [WebhookController::class, 'handle']);
Route::get('/products', [ProductController::class, 'index']); 


Route::post('/boxes', [BoxController::class, 'store']);