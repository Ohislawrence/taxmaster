<?php

use App\Http\Controllers\BusinessSetupController;
use App\Http\Controllers\Webhooks\MonoWebhookController;
use App\Http\Controllers\TestMonoController;
use App\Http\Controllers\Public\BlogController;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\PublicApi\PublicAiController;

use App\Http\Controllers\PublicApi\VisitorChatController;

// Blog public routes
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

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
// Features page
Route::get('/features', function () {
    return Inertia::render('Public/Features', [
        'title' => 'Features',
    ]);
})->name('features');
// Tax calculator landing page (marketing + estimator for Nigerian businesses)
Route::get('/tax-calculator', function () {
    return Inertia::render('Public/TaxCalculator', [
        'title' => 'Tax Calculator - Estimate your Nigerian business taxes',
    ]);
})->name('tax-calculator');
// Accountant features landing page
Route::get('/for-accountants', function () {
    return Inertia::render('Public/AccountantFeatures', [
        'title' => 'TaxMaster for Accountants',
    ]);
})->name('accountant.features');

// Businesses features landing page
Route::get('/for-businesses', function () {
    return Inertia::render('Public/BusinessFeatures', [
        'title' => 'TaxMaster for Businesses',
    ]);
})->name('business.features');

// E-Invoicing feature page
Route::get('/e-invoicing', function () {
    return Inertia::render('Public/EInvoicing', [
        'title' => 'FIRS E-Invoicing - Nigerian Compliant Electronic Invoicing',
        'description' => 'Generate FIRS-compliant e-invoices with digital signatures, automated submission, and TIN validation. UBL 2.1 standard with manual export options.',
    ]);
})->name('einvoicing');

Route::get('/about', fn () => Inertia::render('Public/About', ['title' => 'About']))->name('about');
Route::get('/contact', fn () => Inertia::render('Public/Contact', ['title' => 'Contact']))->name('contact');
Route::post('/contact', [App\Http\Controllers\Public\ContactController::class, 'send'])->name('contact.send');
Route::get('/help', fn () => Inertia::render('Public/HelpCenter', ['title' => 'Help Center']))->name('help');

// Legal & compliance pages (serve markdown from resources/markdown)
use Illuminate\Support\Facades\File as FileFacade;

Route::get('/privacy', function () {
    $path = resource_path('markdown/policy.md');
    $content = FileFacade::exists($path) ? FileFacade::get($path) : null;
    return Inertia::render('Public/MarkdownPage', [
        'title' => 'Privacy Policy',
        'markdown' => $content,
    ]);
})->name('privacy');

Route::get('/terms', function () {
    $path = resource_path('markdown/terms.md');
    $content = FileFacade::exists($path) ? FileFacade::get($path) : null;
    return Inertia::render('Public/MarkdownPage', [
        'title' => 'Terms of Service',
        'markdown' => $content,
    ]);
})->name('terms');
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

// Affiliate link handler - sets affiliate code in session then redirects to pricing
Route::get('/affiliate/{code}', function ($code) {
    session(['affiliate_code' => $code]);
    return redirect()->route('pricing');
})->name('affiliate.link');

// Signed file serve route (used as fallback when storage driver doesn't support temporary URLs)
Route::get('/files/serve/{encodedPath}', [App\Http\Controllers\Business\FileController::class, 'serve'])->name('files.serve');


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
    // Profile route: render role-appropriate profile page
    Route::get('/profile', function () {
        $user = auth()->user();
        if ($user && method_exists($user, 'hasRole')) {
            if ($user->hasRole('admin')) {
                return Inertia::render('Admin/Profile', ['user' => $user]);
            }
            if ($user->hasRole('accountant')) {
                return Inertia::render('Accountant/Profile', ['user' => $user]);
            }
            if ($user->hasRole('business')) {
                return Inertia::render('Business/Profile', ['user' => $user]);
            }
        }
        return Inertia::render('Profile/Show', ['user' => $user]);
    })->name('profile.show');
    Route::get('/dashboard', function () {
        $user = auth()->user();

        // Redirect based on user role
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('business')) {
            return redirect()->route('business.dashboard');
        } elseif ($user->hasRole('accountant')) {
            return redirect()->route('accountant.dashboard');
        }

        // Fallback to default dashboard
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Test Mono Integration (localhost/test-mono)
    Route::get('/test-mono', [TestMonoController::class, 'testMono'])->name('test.mono');

    // Business switch (available to authenticated users who can manage businesses)
    Route::post('/business/switch', [App\Http\Controllers\Business\SwitchController::class, 'switch'])->name('business.switch');
    Route::post('/business/leave', [App\Http\Controllers\Business\SwitchController::class, 'leave'])->name('business.leave');
});

// Include admin routes
require __DIR__ . '/admin.php';

// Include business routes
require __DIR__ . '/business.php';
// Include accountant routes
require __DIR__ . '/accountant.php';
