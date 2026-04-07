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
use App\Http\Controllers\Business\DataExportController;
use App\Http\Controllers\Business\VatController;
use App\Http\Controllers\Business\PayeController;
use App\Http\Controllers\Business\CitController;
use App\Http\Controllers\Business\WhtController;
use App\Http\Controllers\Business\FinancialStatementController;
use App\Http\Controllers\Business\CacFormController;
use App\Http\Controllers\BusinessSetupController;
use App\Http\Controllers\Business\GetStartedController;
use App\Http\Controllers\Business\IntegrationsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Business\TaxInsightsController;

use App\Http\Middleware\EnsureBusinessOrManager;

Route::middleware(['auth:sanctum', 'verified', EnsureBusinessOrManager::class, 'ensure.business.setup', 'ensure.subscription'])->prefix('business')->name('business.')->group(function () {
    // ERP Connectors (Phase 3)
    Route::post('erp/sync/quickbooks', [App\Http\Controllers\Business\ERPController::class, 'syncQuickBooks']);
    Route::post('erp/sync/xero', [App\Http\Controllers\Business\ERPController::class, 'syncXero']);
    Route::post('erp/sync/sage', [App\Http\Controllers\Business\ERPController::class, 'syncSage']);

    //Add business
    Route::get('setup', [BusinessSetupController::class, 'create'])->name('setup');
    Route::post('setup', [BusinessSetupController::class, 'store'])->name('setup.store');


    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Invoices (Phase 1 e-invoicing)
    // List invoices for business
    Route::get('invoices', [App\Http\Controllers\Business\InvoiceController::class, 'index'])->name('invoices.index');
    // Create sales invoice (business) - must come before the parameterized {invoice} route
    Route::get('invoices/create', [App\Http\Controllers\Business\SalesInvoiceController::class, 'create'])->name('invoices.create');
    Route::post('invoices', [App\Http\Controllers\Business\SalesInvoiceController::class, 'store'])->name('invoices.store');
    // Edit/update invoice
    Route::get('invoices/{invoice}/edit', [App\Http\Controllers\Business\SalesInvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('invoices/{invoice}', [App\Http\Controllers\Business\SalesInvoiceController::class, 'update'])->name('invoices.update');
    Route::patch('invoices/{invoice}/status', [App\Http\Controllers\Business\SalesInvoiceController::class, 'updateStatus'])->name('invoices.update-status');
    Route::post('invoices/{invoice}/mark-paid', [App\Http\Controllers\Business\SalesInvoiceController::class, 'markPaid'])->name('invoices.mark-paid');

    Route::get('invoices/{invoice}', [App\Http\Controllers\Business\InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/jades', [App\Http\Controllers\Business\InvoiceController::class, 'generateJadesInvoice'])->name('invoices.jades');
    Route::get('invoices/{invoice}/qr', [App\Http\Controllers\Business\InvoiceController::class, 'qr']);
    // Signed PDF (public business route) - provide direct access without transactions middleware
    Route::get('invoices/{invoice}/pdf/signed', [App\Http\Controllers\Business\FileController::class, 'invoicePdfSigned'])->name('invoices.pdf.signed.business');

    // FIRS E-Invoicing Integration
    Route::post('invoices/{invoice}/submit-to-firs', [App\Http\Controllers\Business\SalesInvoiceController::class, 'submitToFirs'])->name('invoices.submit-to-firs');
    Route::get('invoices/{invoice}/firs-status', [App\Http\Controllers\Business\SalesInvoiceController::class, 'firsStatus'])->name('invoices.firs-status');
    Route::post('validate-tin', [App\Http\Controllers\Business\SalesInvoiceController::class, 'validateTin'])->name('validate-tin');

    // Manual FIRS Export (for when API keys are not configured)
    Route::get('invoices/{invoice}/export/ubl-xml', [App\Http\Controllers\Business\SalesInvoiceController::class, 'exportUblXml'])->name('invoices.export.ubl-xml');
    Route::get('invoices/{invoice}/export/ubl-json', [App\Http\Controllers\Business\SalesInvoiceController::class, 'exportUblJson'])->name('invoices.export.ubl-json');

    // Get Started Guide
    Route::prefix('get-started')->name('get-started.')->group(function () {
        Route::get('/', [GetStartedController::class, 'index'])->name('index');
        Route::post('/complete-step', [GetStartedController::class, 'completeStep'])->name('complete-step');
        Route::post('/incomplete-step', [GetStartedController::class, 'incompleteStep'])->name('incomplete-step');
        Route::post('/dismiss', [GetStartedController::class, 'dismiss'])->name('dismiss');
        Route::post('/undismiss', [GetStartedController::class, 'undismiss'])->name('undismiss');
    });

    // AI Tax Workflows
    Route::prefix('ai-workflows')->name('ai-workflows.')->group(function () {
        Route::get('/', [App\Http\Controllers\Business\AiWorkflowController::class, 'index'])->name('index');
        Route::get('/types', [App\Http\Controllers\Business\AiWorkflowController::class, 'types'])->name('types');
        Route::get('/statistics', [App\Http\Controllers\Business\AiWorkflowController::class, 'statistics'])->name('statistics');
        Route::post('/check-availability', [App\Http\Controllers\Business\AiWorkflowController::class, 'checkAvailability'])->name('check-availability');
        Route::post('/', [App\Http\Controllers\Business\AiWorkflowController::class, 'store'])->name('store');
        Route::get('/{workflow}', [App\Http\Controllers\Business\AiWorkflowController::class, 'show'])->name('show');
        Route::post('/{workflow}/retry', [App\Http\Controllers\Business\AiWorkflowController::class, 'retry'])->name('retry');
        Route::post('/{workflow}/cancel', [App\Http\Controllers\Business\AiWorkflowController::class, 'cancel'])->name('cancel');
        Route::post('/{workflow}/review', [App\Http\Controllers\Business\AiWorkflowController::class, 'review'])->name('review');
        Route::delete('/{workflow}', [App\Http\Controllers\Business\AiWorkflowController::class, 'destroy'])->name('destroy');
    });

    // Tax Returns
    Route::resource('tax-returns', TaxReturnController::class);
    Route::post('tax-returns/{taxReturn}/submit', [TaxReturnController::class, 'submit'])->name('tax-returns.submit');
    Route::get('tax-returns/{taxReturn}/analysis', [TaxReturnController::class, 'getAnalysis'])->name('tax-returns.analysis');
    Route::post('tax-returns/{taxReturn}/analyze', [TaxReturnController::class, 'analyze'])->name('tax-returns.analyze');
    Route::get('tax-returns/{taxReturn}/export-pdf', [TaxReturnController::class, 'exportPdf'])->name('tax-returns.export-pdf');

    // Payments - Hidden until tax payment functionality is available
    // Route::resource('payments', PaymentController::class)->only(['index', 'show', 'create']);
    // Route::post('payments/{payment}/initialize', [PaymentController::class, 'initialize'])->name('payments.initialize');
    // Route::get('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    // Route::post('payments/webhook/paystack', [PaymentController::class, 'webhookPaystack'])->name('payments.webhook.paystack');

    // Staff Management
    Route::get('staff/bulk-upload', [StaffController::class, 'bulkUploadForm'])->name('staff.bulk-upload');
    Route::post('staff/bulk-upload/map-columns', [StaffController::class, 'mapColumns'])->name('staff.bulk-upload.map-columns');
    Route::post('staff/bulk-upload', [StaffController::class, 'processBulkUpload'])->name('staff.bulk-upload.process');
    Route::get('staff/download-template', [StaffController::class, 'downloadTemplate'])->name('staff.download-template');
    Route::resource('staff', StaffController::class);
    Route::get('staff/{staff}/tax-analysis', [StaffController::class, 'taxAnalysis'])->name('staff.tax-analysis');
    Route::get('staff/{staff}/payslip', [StaffController::class, 'generatePayslip'])->name('staff.payslip');
    Route::get('staff/{staff}/payslip/{year}/{month}', [StaffController::class, 'generatePayslip'])->name('staff.payslip.period');

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('settings/vat-exempt', [SettingsController::class, 'updateVatExemptStatus'])->name('settings.update-vat-exempt');
    Route::get('settings/activity', [SettingsController::class, 'activityLog'])->name('settings.activity');

    // NDPA Data Portability (Article 25)
    Route::get('settings/export-data', [DataExportController::class, 'export'])->name('settings.export-data');

    //   & Plans
    // Redirect /business/plans to public /pricing page
    Route::redirect('plans', '/pricing')->name('plans.index')->withoutMiddleware(['ensure.business.setup', 'ensure.subscription']);
    Route::get('plans/{plan}', [SubscribeController::class, 'selectPlan'])->name('plans.select')->withoutMiddleware('ensure.subscription');
    Route::post('plans/{plan}/checkout', [SubscribeController::class, 'processCheckout'])->name('plans.checkout')->withoutMiddleware('ensure.subscription');
    Route::get('plans/payment/callback', [SubscribeController::class, 'paymentCallback'])->name('plans.payment-callback')->withoutMiddleware(['ensure.business.setup', 'ensure.subscription']);
    Route::post('subscription/upgrade', [SubscribeController::class, 'upgrade'])->name('subscription.upgrade')->withoutMiddleware('ensure.subscription');
    Route::post('subscription/cancel', [SubscribeController::class, 'cancel'])->name('subscription.cancel');
    Route::get('subscription', [SettingsController::class, 'subscription'])->name('subscription')->withoutMiddleware('ensure.subscription');
    Route::post('subscription/upgrade-plan', [SettingsController::class, 'upgradePlan'])->name('subscription.upgrade-plan')->withoutMiddleware('ensure.subscription');
    Route::get('subscription/payment/callback', [SettingsController::class, 'paymentCallback'])->name('subscription.payment-callback')->withoutMiddleware(['ensure.business.setup', 'ensure.subscription']);

    // AI Module - open to all business users (no subscription restriction)
    Route::get('ai/insights', [AiController::class, 'insights'])->name('ai.insights');
    Route::get('ai/chat', [AiController::class, 'chat'])->name('ai.chat');
    Route::post('ai/chat/send', [AiController::class, 'sendMessage'])->name('ai.chat.send');
    Route::get('ai/history', [AiController::class, 'getHistory'])->name('ai.history');
    Route::post('ai/history/clear', [AiController::class, 'clearHistory'])->name('ai.history.clear');
    Route::middleware('subscription.features:use_ai_optimization')->group(function () {
        Route::post('ai/tax-returns/{taxReturn}/analyze', [AiController::class, 'analyzeTaxReturn'])->name('ai.tax-analyze');
        Route::post('ai/tax-returns/{taxReturn}/optimize', [AiController::class, 'getTaxOptimizationRecommendations'])->name('ai.tax-optimize');
    });

    // Tax Insights (simple trends view + data endpoint)
    Route::get('insights', function () {
        return Inertia::render('Business/Insights/TaxTrends');
    })->name('insights.index');

    // Business invites: allow business owners to invite accountants
    Route::post('invite/accountant', [App\Http\Controllers\Business\AccountantInviteController::class, 'store'])->name('invite.accountant');
    Route::get('invites', [App\Http\Controllers\Business\AccountantInviteController::class, 'index'])->name('invites.index');
    Route::delete('invites/{invite}', [App\Http\Controllers\Business\AccountantInviteController::class, 'destroy'])->name('invites.revoke');
    Route::post('accountant/detach', [App\Http\Controllers\Business\AccountantInviteController::class, 'detachAccountant'])->name('accountant.detach');

    Route::get('insights/tax-trends', [TaxInsightsController::class, 'taxTrends'])->name('insights.tax-trends');
    Route::get('insights/summary', [TaxInsightsController::class, 'summary'])->name('insights.summary');
    Route::get('insights/anomalies', [TaxInsightsController::class, 'anomalies'])->name('insights.anomalies');

        // Bank Accounts & Transactions (Phase 1)
        Route::prefix('banks')->name('banks.')->middleware('subscription.features:link_bank_account')->group(function () {
            Route::get('/', [BankAccountController::class, 'index'])->name('index');
            Route::post('/callback', [BankAccountController::class, 'callback'])->name('callback');
            Route::post('/{bankAccount}/sync', [BankAccountController::class, 'sync'])->name('sync');
            Route::post('/{bankAccount}/toggle-auto-sync', [BankAccountController::class, 'toggleAutoSync'])->name('toggle-auto-sync');
            Route::delete('/{bankAccount}', [BankAccountController::class, 'destroy'])->name('destroy');
        });

        // Integrations Hub
        Route::get('integrations', [App\Http\Controllers\Business\IntegrationsController::class, 'index'])->name('integrations.index');

        // QuickBooks Integration (Accounting Software)
        Route::prefix('integrations/quickbooks')->name('integrations.quickbooks.')->middleware('subscription.features:link_bank_account')->group(function () {
            Route::get('/', [App\Http\Controllers\Business\QuickBooksController::class, 'index'])->name('index');
            Route::post('/credentials', [App\Http\Controllers\Business\QuickBooksController::class, 'saveCredentials'])->name('save-credentials');
            Route::get('/connect', [App\Http\Controllers\Business\QuickBooksController::class, 'connect'])->name('connect');
            Route::get('/callback', [App\Http\Controllers\Business\QuickBooksController::class, 'callback'])->name('callback');
            Route::post('/disconnect', [App\Http\Controllers\Business\QuickBooksController::class, 'disconnect'])->name('disconnect');
            Route::post('/sync', [App\Http\Controllers\Business\QuickBooksController::class, 'sync'])->name('sync');
            Route::patch('/settings', [App\Http\Controllers\Business\QuickBooksController::class, 'updateSettings'])->name('update-settings');
            Route::get('/logs/{log}', [App\Http\Controllers\Business\QuickBooksController::class, 'getSyncLog'])->name('sync-log');
        });

        // Zoho Books Integration (Accounting Software)
        Route::prefix('integrations/zoho')->name('integrations.zoho.')->middleware('subscription.features:link_bank_account')->group(function () {
            Route::get('/', [App\Http\Controllers\Business\ZohoController::class, 'index'])->name('index');
            Route::post('/credentials', [App\Http\Controllers\Business\ZohoController::class, 'saveCredentials'])->name('save-credentials');
            Route::get('/connect', [App\Http\Controllers\Business\ZohoController::class, 'connect'])->name('connect');
            Route::get('/callback', [App\Http\Controllers\Business\ZohoController::class, 'callback'])->name('callback');
            Route::post('/disconnect', [App\Http\Controllers\Business\ZohoController::class, 'disconnect'])->name('disconnect');
            Route::post('/sync', [App\Http\Controllers\Business\ZohoController::class, 'sync'])->name('sync');
            Route::patch('/settings', [App\Http\Controllers\Business\ZohoController::class, 'updateSettings'])->name('update-settings');
        });

        // Transactions - Open to all plans (basic transaction tracking is included in Free plan)
        Route::prefix('transactions')->name('transactions.')->group(function () {
            // Transaction import (CSV/Excel) with AI mapping
            Route::get('/import', [App\Http\Controllers\Business\TransactionImportController::class, 'showImportForm'])->name('import.form');
            Route::post('/import/parse', [App\Http\Controllers\Business\TransactionImportController::class, 'parseFile'])->name('import.parse');
            Route::post('/import/map-columns', [App\Http\Controllers\Business\TransactionImportController::class, 'mapColumns'])->name('import.map-columns');
            Route::post('/import/process', [App\Http\Controllers\Business\TransactionImportController::class, 'processImport'])->name('import.process');
            Route::get('/', [TransactionController::class, 'index'])->name('index');
            Route::get('/{transaction}/categorize', [TransactionController::class, 'categorize'])->name('categorize');
            Route::put('/{transaction}/category', [TransactionController::class, 'updateCategory'])->name('update-category');
            Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->name('destroy');
            Route::post('/batch-categorize', [TransactionController::class, 'batchCategorize'])->name('batch-categorize');
            // Reconciliations review
            Route::get('/reconciliations', [App\Http\Controllers\Business\ReconciliationController::class, 'index'])->name('reconciliations.index');
            Route::post('/reconciliations/{reconciliation}/confirm', [App\Http\Controllers\Business\ReconciliationController::class, 'confirm'])->name('reconciliations.confirm');
            Route::post('/reconciliations/{reconciliation}/reject', [App\Http\Controllers\Business\ReconciliationController::class, 'reject'])->name('reconciliations.reject');
                // Signed URLs for attachments and PDFs
                Route::get('/reconciliations/{reconciliation}/attachment/{index}/signed', [App\Http\Controllers\Business\FileController::class, 'reconciliationAttachment'])->name('reconciliations.attachment.signed');
            // Export requires Basic+ plan
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
            Route::get('/{vatReturn}/export-pdf', [VatController::class, 'exportPdf'])->name('export-pdf');
            // VAT Form 002 exports (business)
            Route::get('/export/form002', [VatController::class, 'exportForm002'])->name('export.form002');
            Route::get('/{vatReturn}/export/form002', [VatController::class, 'exportForm002ForReturn'])->name('export.form002.single');
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
            Route::get('/{citReturn}/export-pdf', [CitController::class, 'exportPdf'])->name('export-pdf');
            Route::post('/calculate-preview', [CitController::class, 'calculatePreview'])->name('calculate-preview');
        });

        // PAYE Returns (Phase 2)
        Route::prefix('paye')->name('paye.')->group(function () {
            Route::get('/', [PayeController::class, 'index'])->name('index');
            Route::get('/create', [PayeController::class, 'create'])->name('create');
            Route::post('/', [PayeController::class, 'store'])->name('store');
            Route::post('/form-h1', [PayeController::class, 'generateFormH1'])->name('form-h1');
            Route::get('/{payeReturn}', [PayeController::class, 'show'])->name('show');
            Route::put('/{payeReturn}/status', [PayeController::class, 'updateStatus'])->name('update-status');
            Route::post('/{payeReturn}/generate-rrr', [PayeController::class, 'generatePaymentRRR'])->name('generate-rrr');
            Route::get('/{payeReturn}/export-pdf', [PayeController::class, 'exportPdf'])->name('export-pdf');
            // PAYE schedules export (business)
            Route::get('/{payeReturn}/export-schedules', [PayeController::class, 'exportSchedules'])->name('export.schedules');
            Route::get('/export/schedules', [PayeController::class, 'exportSchedulesBulk'])->name('export.schedules.bulk');
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
            Route::get('/returns/{whtReturn}/export-pdf', [WhtController::class, 'exportReturnPdf'])->name('return.export-pdf');
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
