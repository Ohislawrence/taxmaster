<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Business\SettingsController;
use App\Http\Controllers\Api\BlogPostController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Paystack webhook for subscription payments
Route::post('/webhooks/paystack/subscription', [SettingsController::class, 'webhook'])->withoutMiddleware('api');

// Blog API
Route::get('/blog-posts', [BlogPostController::class, 'index']);
Route::get('/blog-posts/{id}', [BlogPostController::class, 'show']);

// Protected blog actions (require auth)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/blog-posts', [BlogPostController::class, 'store']);
    Route::put('/blog-posts/{id}', [BlogPostController::class, 'update']);
    Route::delete('/blog-posts/{id}', [BlogPostController::class, 'destroy']);
});
