<?php

use App\Http\Controllers\BusinessSetupController;
use App\Http\Controllers\Webhooks\MonoWebhookController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Mono webhook (no auth)
Route::post('/webhooks/mono', [MonoWebhookController::class, 'handle'])->name('webhooks.mono');

// Business Setup Routes (after email verification)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/business-setup', [BusinessSetupController::class, 'create'])->name('business.setup.create');
    Route::post('/business-setup', [BusinessSetupController::class, 'store'])->name('business.setup.store');
});

// Protected Routes (after business setup)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'ensure.business.setup',
])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();

        // Redirect based on user role
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('business')) {
            return redirect()->route('business.dashboard');
        }

        // Fallback to default dashboard
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

// Include admin routes
require __DIR__ . '/admin.php';

// Include business routes
require __DIR__ . '/business.php';
