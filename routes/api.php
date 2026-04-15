<?php
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoxController;

Route::post('/webhook', [WebhookController::class, 'handle']);
Route::get('/public/boxes/{id}', [BoxController::class, 'details']);

