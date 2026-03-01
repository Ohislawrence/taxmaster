<?php

use App\Http\Controllers\Business\DashboardController;
use App\Http\Controllers\Business\TaxReturnController;
use App\Http\Controllers\Business\PaymentController;
use App\Http\Controllers\Business\StaffController;
use App\Http\Controllers\Business\SettingsController;
use App\Http\Controllers\Business\AiController;
use App\Http\Controllers\Business\SubscribeController;
use App\Http\Controllers\Business\BankAccountController;
use App\Http\Controllers\Business\TransactionController;
use App\Http\Controllers\Business\ComplianceController;
use App\Http\Controllers\Business\VatController;
use App\Http\Controllers\Business\PayeController;
use App\Http\Controllers\Business\CitController;
use App\Http\Controllers\Business\WhtController;
use App\Http\Controllers\Business\FinancialStatementController;
use App\Http\Controllers\Business\CacFormController;
use App\Http\Controllers\Business\BusinessSetupController;
use App\Http\Controllers\Business\GetStartedController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified', 'business', 'ensure.business.setup', 'ensure.subscription'])->prefix('business')->name('business.')->group(function () {

    //Add business
    Route::get('setup', [BusinessSetupController::class, 'create'])->name('setup');
    Route::post('setup', [BusinessSetupController::class, 'store'])->name('setup.store');


    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Get Started Guide
    Route::prefix('get-started')->name('get-started.')->group(function () {
        Route::get('/', [GetStartedController::class, 'index'])->name('index');
        Route::post('/complete-step', [GetStartedController::class, 'completeStep'])->name('complete-step');
        Route::post('/incomplete-step', [GetStartedController::class, 'incompleteStep'])->name('incomplete-step');
        Route::post('/dismiss', [GetStartedController::class, 'dismiss'])->name('dismiss');
        Route::post('/undismiss', [GetStartedController::class, 'undismiss'])->name('undismiss');
    });

    // Tax Returns
    Route::resource('tax-returns', TaxReturnController::class);
    Route::post('tax-returns/{taxReturn}/submit', [TaxReturnController::class, 'submit'])->name('tax-returns.submit');
    Route::get('tax-returns/{taxReturn}/analysis', [TaxReturnController::class, 'getAnalysis'])->name('tax-returns.analysis');
    Route::post('tax-returns/{taxReturn}/analyze', [TaxReturnController::class, 'analyze'])->name('tax-returns.analyze');
    Route::get('tax-returns/{taxReturn}/export-pdf', [TaxReturnController::class, 'exportPdf'])->name('tax-returns.export-pdf');

    // Payments
    Route::resource('payments', PaymentController::class)->only(['index', 'show', 'create']);
    Route::post('payments/{payment}/initialize', [PaymentController::class, 'initialize'])->name('payments.initialize');
    Route::get('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::post('payments/webhook/paystack', [PaymentController::class, 'webhookPaystack'])->name('payments.webhook.paystack');

    // Staff Management
    Route::resource('staff', StaffController::class);
    Route::get('staff/{staff}/tax-analysis', [StaffController::class, 'taxAnalysis'])->name('staff.tax-analysis');

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('settings/activity', [SettingsController::class, 'activityLog'])->name('settings.activity');

    // Subscription & Plans
    Route::get('plans', [SubscribeController::class, 'showPlans'])->name('plans.index')->withoutMiddleware(['ensure.business.setup', 'ensure.subscription']);
    Route::get('plans/{plan}', [SubscribeController::class, 'selectPlan'])->name('plans.select')->withoutMiddleware('ensure.subscription');
    Route::post('plans/{plan}/checkout', [SubscribeController::class, 'processCheckout'])->name('plans.checkout')->withoutMiddleware('ensure.subscription');
    Route::get('plans/payment/callback', [SubscribeController::class, 'paymentCallback'])->name('plans.payment-callback')->withoutMiddleware(['ensure.business.setup', 'ensure.subscription']);
    Route::post('subscription/upgrade', [SubscribeController::class, 'upgrade'])->name('subscription.upgrade')->withoutMiddleware('ensure.subscription');
    Route::post('subscription/cancel', [SubscribeController::class, 'cancel'])->name('subscription.cancel');
    Route::get('subscription', [SettingsController::class, 'subscription'])->name('subscription')->withoutMiddleware('ensure.subscription');
    Route::post('subscription/upgrade-plan', [SettingsController::class, 'upgradePlan'])->name('subscription.upgrade-plan')->withoutMiddleware('ensure.subscription');

    // AI Module
    Route::middleware('subscription.features:use_ai_analysis')->group(function () {
        Route::get('ai/insights', [AiController::class, 'insights'])->name('ai.insights');
    });
    Route::middleware('subscription.features:use_ai_chat')->group(function () {
        Route::get('ai/chat', [AiController::class, 'chat'])->name('ai.chat');
        Route::post('ai/chat/send', [AiController::class, 'sendMessage'])->name('ai.chat.send');
        Route::get('ai/history', [AiController::class, 'getHistory'])->name('ai.history');
    });
    Route::middleware('subscription.features:use_ai_optimization')->group(function () {
        Route::post('ai/tax-returns/{taxReturn}/analyze', [AiController::class, 'analyzeTaxReturn'])->name('ai.tax-analyze');
        Route::post('ai/tax-returns/{taxReturn}/optimize', [AiController::class, 'getTaxOptimizationRecommendations'])->name('ai.tax-optimize');
    });

        // Bank Accounts & Transactions (Phase 1)
        Route::prefix('banks')->name('banks.')->middleware('subscription.features:link_bank_account')->group(function () {
            Route::get('/', [BankAccountController::class, 'index'])->name('index');
            Route::post('/callback', [BankAccountController::class, 'callback'])->name('callback');
            Route::post('/{bankAccount}/sync', [BankAccountController::class, 'sync'])->name('sync');
            Route::post('/{bankAccount}/toggle-auto-sync', [BankAccountController::class, 'toggleAutoSync'])->name('toggle-auto-sync');
            Route::delete('/{bankAccount}', [BankAccountController::class, 'destroy'])->name('destroy');
        });

        // Transactions
        Route::prefix('transactions')->name('transactions.')->middleware('subscription.features:link_bank_account')->group(function () {
            Route::get('/', [TransactionController::class, 'index'])->name('index');
            Route::get('/{transaction}/categorize', [TransactionController::class, 'categorize'])->name('categorize');
            Route::put('/{transaction}/category', [TransactionController::class, 'updateCategory'])->name('update-category');
            Route::post('/batch-categorize', [TransactionController::class, 'batchCategorize'])->name('batch-categorize');
            Route::middleware('subscription.features:export_pdf')->get('/export', [TransactionController::class, 'export'])->name('export');
        });

        // Compliance Calendar
        Route::prefix('compliance')->name('compliance.')->group(function () {
            Route::get('/', [ComplianceController::class, 'index'])->name('index');
            Route::get('/{deadline}', [ComplianceController::class, 'show'])->name('show');
            Route::post('/{deadline}/complete', [ComplianceController::class, 'complete'])->name('complete');
            Route::post('/{deadline}/dismiss', [ComplianceController::class, 'dismiss'])->name('dismiss');
            Route::post('/{deadline}/upload', [ComplianceController::class, 'uploadAttachment'])->name('upload');
            Route::post('/regenerate', [ComplianceController::class, 'regenerate'])->name('regenerate');
        });

        // VAT Returns (Phase 3B.2)
        Route::prefix('vat')->name('vat.')->middleware('subscription.features:file_vat')->group(function () {
            Route::get('/', [VatController::class, 'index'])->name('index');
            Route::get('/create', [VatController::class, 'create'])->name('create');
            Route::post('/', [VatController::class, 'store'])->name('store');
            Route::get('/{vatReturn}', [VatController::class, 'show'])->name('show');
            Route::get('/{vatReturn}/edit', [VatController::class, 'edit'])->name('edit');
            Route::put('/{vatReturn}', [VatController::class, 'update'])->name('update');
            Route::put('/{vatReturn}/status', [VatController::class, 'updateStatus'])->name('update-status');
            Route::post('/{vatReturn}/generate-rrr', [VatController::class, 'generatePaymentRRR'])->name('generate-rrr');
            Route::post('/calculate-preview', [VatController::class, 'calculatePreview'])->name('calculate-preview');
        });

        // CIT Returns (Phase 3B.1)
        Route::prefix('cit')->name('cit.')->middleware('subscription.features:file_cit')->group(function () {
            Route::get('/', [CitController::class, 'index'])->name('index');
            Route::get('/create', [CitController::class, 'create'])->name('create');
            Route::post('/', [CitController::class, 'store'])->name('store');
            Route::get('/{citReturn}', [CitController::class, 'show'])->name('show');
            Route::get('/{citReturn}/edit', [CitController::class, 'edit'])->name('edit');
            Route::put('/{citReturn}', [CitController::class, 'update'])->name('update');
            Route::put('/{citReturn}/status', [CitController::class, 'updateStatus'])->name('update-status');
            Route::post('/{citReturn}/generate-rrr', [CitController::class, 'generatePaymentRRR'])->name('generate-rrr');
            Route::post('/calculate-preview', [CitController::class, 'calculatePreview'])->name('calculate-preview');
        });

        // PAYE Returns (Phase 2)
        Route::prefix('paye')->name('paye.')->group(function () {
            Route::get('/', [PayeController::class, 'index'])->name('index');
            Route::get('/create', [PayeController::class, 'create'])->name('create');
            Route::post('/', [PayeController::class, 'store'])->name('store');
            Route::get('/{payeReturn}', [PayeController::class, 'show'])->name('show');
            Route::put('/{payeReturn}/status', [PayeController::class, 'updateStatus'])->name('update-status');
            Route::post('/{payeReturn}/generate-rrr', [PayeController::class, 'generatePaymentRRR'])->name('generate-rrr');
            Route::post('/calculate-preview', [PayeController::class, 'calculatePreview'])->name('calculate-preview');
        });

        // WHT Transactions & Returns (Phase 2)
        Route::prefix('wht')->name('wht.')->group(function () {
            // Transactions
            Route::get('/', [WhtController::class, 'index'])->name('index');
            Route::get('/create', [WhtController::class, 'create'])->name('create');
            Route::post('/', [WhtController::class, 'store'])->name('store');
            Route::get('/transactions/{whtTransaction}', [WhtController::class, 'show'])->name('show');
            Route::put('/transactions/{whtTransaction}', [WhtController::class, 'update'])->name('update');
            Route::delete('/transactions/{whtTransaction}', [WhtController::class, 'destroy'])->name('destroy');

            // Returns
            Route::get('/returns', [WhtController::class, 'returns'])->name('returns');
            Route::post('/returns/generate', [WhtController::class, 'generateReturn'])->name('returns.generate');
            Route::get('/returns/{whtReturn}', [WhtController::class, 'showReturn'])->name('return.show');
            Route::put('/returns/{whtReturn}/status', [WhtController::class, 'updateReturnStatus'])->name('return.update-status');
            Route::post('/returns/{whtReturn}/generate-rrr', [WhtController::class, 'generateReturnPaymentRRR'])->name('return.generate-rrr');

            // AJAX helpers
            Route::post('/calculate-preview', [WhtController::class, 'calculatePreview'])->name('calculate-preview');
            Route::post('/period-summary', [WhtController::class, 'getPeriodSummary'])->name('period-summary');
        });

        // Financial Statements & CAC Forms
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::middleware('subscription.features:generate_financial_statements')->group(function () {
                Route::get('/financial-statements', [FinancialStatementController::class, 'index'])->name('financial-statements');
                Route::post('/financial-statements/pdf', [FinancialStatementController::class, 'downloadPdf'])->name('financial-statements.pdf');
            });
            Route::middleware('subscription.features:generate_cac_forms')->group(function () {
                Route::get('/cac-forms', [CacFormController::class, 'index'])->name('cac-forms');
                Route::post('/cac-forms/pdf', [CacFormController::class, 'downloadPdf'])->name('cac-forms.pdf');
            });
        });
});
