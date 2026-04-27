<?php
use App\Http\Controllers\BoxController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) return redirect('/boxes');
    return redirect()->route('oauth.redirect');
});

Auth::routes();

Route::get('/oauth/redirect', [OAuthController::class, 'redirect'])->name('oauth.redirect');
Route::get('/oauth/callback', [OAuthController::class, 'callback'])->name('oauth.callback');
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/api/products', [ProductController::class, 'index']);
    Route::get('/api/products/{id}', [ProductController::class, 'show']);

    Route::get('/boxes', [BoxController::class, 'index']);
    Route::get('/boxes/{id}', [BoxController::class, 'show']);
    Route::delete('/boxes/{id}', [BoxController::class, 'destroy']);
    Route::get('/boxes/{id}/edit', [BoxController::class, 'edit']);

    Route::get('/api/boxes', [BoxController::class, 'index']);
    Route::get('/api/boxes/{id}', [BoxController::class, 'show']);
    Route::post('/api/boxes', [BoxController::class, 'store']);
    Route::put('/api/boxes/{id}', [BoxController::class, 'update']);
});
