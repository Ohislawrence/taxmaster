<?php

use App\Http\Controllers\BusinessSetupController;
use App\Http\Controllers\Webhooks\MonoWebhookController;
use App\Http\Controllers\TestMonoController;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\PublicApi\PublicAiController;

use App\Http\Controllers\PublicApi\VisitorChatController;

// Blog public routes
Route::get('/blog', fn () => Inertia::render('Blog/Index', [
    'title' => 'Blog',
]))->name('blog.index');
Route::get('/blog/{slug}', fn ($slug) => Inertia::render('Blog/Show', [
    'slug' => $slug,
    'title' => $slug,
]))->name('blog.show');

Route::get('/', function () {
    return Inertia::render('Home', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'title' => 'Simplifying tax compliance for Nigerian businesses',
    ]);
})->name('home');

// Public marketing & legal pages
Route::get('/pricing', function () {
    $plans = SubscriptionPlan::active()->get();
    return Inertia::render('Public/Pricing', [
        'plans' => $plans,
        'title' => 'Pricing',
    ]);
})->name('pricing');
Route::get('/about', fn () => Inertia::render('Public/About', ['title' => 'About']))->name('about');
Route::get('/contact', fn () => Inertia::render('Public/Contact', ['title' => 'Contact']))->name('contact');

// Legal & compliance pages
Route::get('/privacy', fn () => Inertia::render('Public/Privacy', ['title' => 'Privacy']))->name('privacy');
Route::get('/terms', fn () => Inertia::render('Public/Terms', ['title' => 'Terms']))->name('terms');
Route::get('/data-protection', fn () => Inertia::render('Public/DataProtection', ['title' => 'Data Protection']))->name('data-protection');
Route::get('/cookie-policy', function () {
    // If the CookiePolicy.vue page exists, render it; otherwise, show a placeholder
    if (file_exists(resource_path('js/Pages/Public/CookiePolicy.vue'))) {
        return Inertia::render('Public/CookiePolicy', ['title' => 'Cookie Policy']);
    } else {
        return Inertia::render('Public/Privacy', [
            'notice' => 'Cookie Policy coming soon.',
            'title' => 'Cookie Policy (Coming Soon)'
        ]);
    }
})->name('cookie-policy');

// Public AI Visitor Chat endpoint
Route::post('/visitor/ai/chat/send', [VisitorChatController::class, 'sendVisitorMessage']);

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

    // Test Mono Integration (localhost/test-mono)
    Route::get('/test-mono', [TestMonoController::class, 'testMono'])->name('test.mono');
});

// Include admin routes
require __DIR__ . '/admin.php';

// Include business routes
require __DIR__ . '/business.php';
