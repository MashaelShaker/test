<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OAuthController;

// 👇 Web Controller (لصفحات الويب)
use App\Http\Controllers\BoxController;

// 👇 API Controller (للـ JSON routes)
use App\Http\Controllers\Api\BoxController as ApiBoxController;
use App\Http\Controllers\Api\ProductController;

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/boxes');
    }
    return redirect()->route('oauth.redirect');
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Auth::routes();

/*
|--------------------------------------------------------------------------
| OAuth
|--------------------------------------------------------------------------
*/
Route::get('/oauth/redirect', [OAuthController::class, 'redirect'])->name('oauth.redirect');
Route::get('/oauth/callback', [OAuthController::class, 'callback'])->name('oauth.callback');

/*
|--------------------------------------------------------------------------
| Protected Routes (Auth Middleware)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Web Pages (UI)
    |--------------------------------------------------------------------------
    */
    Route::get('/boxes', [BoxController::class, 'index']);
    Route::get('/boxes/{id}', [BoxController::class, 'show']);
    Route::get('/boxes/{id}/edit', [BoxController::class, 'edit']);
    Route::delete('/boxes/{id}', [BoxController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Products API
    |--------------------------------------------------------------------------
    */
    Route::get('/api/products', [ProductController::class, 'index']);
    Route::get('/api/products/{id}', [ProductController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | Boxes API (IMPORTANT FIX)
    |--------------------------------------------------------------------------
    */
    Route::get('/api/boxes', [ApiBoxController::class, 'index']);
    Route::get('/api/boxes/{id}', [ApiBoxController::class, 'show']);
    Route::post('/api/boxes', [ApiBoxController::class, 'store']);
    Route::put('/api/boxes/{id}', [ApiBoxController::class, 'update']);
});
