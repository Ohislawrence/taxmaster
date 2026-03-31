<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BusinessController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\AiSettingsController;
use App\Http\Controllers\Admin\BankAccountController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\ComplianceController;
use App\Http\Controllers\Admin\VatController;
use App\Http\Controllers\Admin\PayeReturnController;
use App\Http\Controllers\Admin\WhtReturnController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\SyncFailureController;
use App\Http\Controllers\Admin\AiAutomationController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BroadcastEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Businesses Management
    Route::resource('businesses', BusinessController::class);
    Route::get('businesses/{business}/invites', [\App\Http\Controllers\Accountant\BusinessInviteController::class, 'index'])->name('businesses.invites');
    Route::get('businesses/{business}/activity', [BusinessController::class, 'activity'])->name('businesses.activity');
    Route::put('businesses/{business}/status', [BusinessController::class, 'updateStatus'])->name('businesses.status');
    Route::post('businesses/{business}/assign-accountant', [BusinessController::class, 'assignAccountant'])->name('businesses.assign-accountant');
    Route::post('businesses/{business}/assign-owner', [BusinessController::class, 'assignOwner'])->name('businesses.assign-owner');

    // Users Management
    Route::resource('users', UserController::class);
    Route::put('users/{user}/password', [UserController::class, 'updatePassword'])->name('users.password');
    Route::put('users/{user}/role', [UserController::class, 'changeRole'])->name('users.change-role');
    Route::post('users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
    Route::post('users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');

    // Accountants management
    Route::get('accountants', [\App\Http\Controllers\Admin\AccountantController::class, 'index'])->name('accountants.index');
    Route::get('accountants/{user}', [\App\Http\Controllers\Admin\AccountantController::class, 'show'])->name('accountants.show');
    Route::post('accountants/{user}/detach/{business}', [\App\Http\Controllers\Admin\AccountantController::class, 'detachBusiness'])->name('accountants.detach');
    Route::post('accountants/{user}/assign', [\App\Http\Controllers\Admin\AccountantController::class, 'assignBusiness'])->name('accountants.assign');
    Route::post('accountants/{user}/transfer/{business}', [\App\Http\Controllers\Admin\AccountantController::class, 'transferOwnership'])->name('accountants.transfer');
    Route::post('accountants/{user}/enable-billing/{business}', [\App\Http\Controllers\Admin\AccountantController::class, 'enableBilling'])->name('accountants.enable-billing');

    // Subscriptions & Plans
    Route::resource('plans', PlanController::class);
    Route::resource('subscriptions', SubscriptionController::class)->only(['index', 'show']);
    Route::post('subscriptions/{subscription}/manage', [SubscriptionController::class, 'manage'])->name('subscriptions.manage');

    // Invoices Management
    Route::resource('invoices', InvoiceController::class)->only(['index', 'show']);

        // Phase 2: PAYE Management (new admin UI)
        Route::get('paye', [\App\Http\Controllers\Admin\PayeController::class, 'index'])->name('paye.index');
        Route::get('paye/{id}', [\App\Http\Controllers\Admin\PayeController::class, 'show'])->name('paye.show');
        Route::get('paye-statistics', [\App\Http\Controllers\Admin\PayeController::class, 'statistics'])->name('paye.statistics');
        Route::get('paye-overdue', [\App\Http\Controllers\Admin\PayeController::class, 'overdueReport'])->name('paye.overdue');
    Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
    Route::post('invoices/{invoice}/resend', [InvoiceController::class, 'resend'])->name('invoices.resend');
    Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::get('invoices/{invoice}/pdf/view', [InvoiceController::class, 'viewPdf'])->name('invoices.pdf.view');
    Route::get('invoices/{invoice}/pdf/download', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf.download');
    Route::get('invoices/{invoice}/jades', [InvoiceController::class, 'generateJadesInvoice'])->name('invoices.jades');

    // Backups Management
    Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('backups', [BackupController::class, 'store'])->name('backups.store');
    Route::get('backups/health', [BackupController::class, 'health'])->name('backups.health');
    Route::post('backups/cleanup', [BackupController::class, 'cleanup'])->name('backups.cleanup');

    // Sync Failures Management
    Route::get('sync-failures', [SyncFailureController::class, 'index'])->name('sync-failures.index');
    Route::get('sync-failures/{notificationId}', [SyncFailureController::class, 'show'])->name('sync-failures.show');
    Route::post('sync-failures/{notificationId}/retry', [SyncFailureController::class, 'retry'])->name('sync-failures.retry');
    Route::post('sync-failures/{notificationId}/resolve', [SyncFailureController::class, 'resolve'])->name('sync-failures.resolve');
    Route::get('bank-accounts/{bankAccount}/sync-status', [SyncFailureController::class, 'bankAccountStatus'])->name('bank-accounts.sync-status');

    // AI Settings
    Route::get('ai-settings', [AiSettingsController::class, 'index'])->name('ai-settings.index');
    Route::put('ai-settings', [AiSettingsController::class, 'update'])->name('ai-settings.update');
    Route::post('ai-settings/test', [AiSettingsController::class, 'testConnection'])->name('ai-settings.test');

    // Accountant role settings
    Route::get('accountant-settings', [\App\Http\Controllers\Admin\AccountantSettingsController::class, 'index'])->name('accountant-settings.index');
    Route::put('accountant-settings', [\App\Http\Controllers\Admin\AccountantSettingsController::class, 'update'])->name('accountant-settings.update');

    // Affiliate payouts management
    Route::get('affiliate/payouts', [\App\Http\Controllers\Admin\AffiliatePayoutController::class, 'index'])->name('affiliate.payouts.index');
    Route::post('affiliate/payouts/{payout}/approve', [\App\Http\Controllers\Admin\AffiliatePayoutController::class, 'approve'])->name('affiliate.payouts.approve');
    Route::post('affiliate/payouts/{payout}/mark-paid', [\App\Http\Controllers\Admin\AffiliatePayoutController::class, 'markPaid'])->name('affiliate.payouts.mark-paid');
    Route::post('affiliate/payouts/bulk-approve', [\App\Http\Controllers\Admin\AffiliatePayoutController::class, 'bulkApprove'])->name('affiliate.payouts.bulk-approve');

    // Affiliate settings (commission rules)
    Route::get('affiliate/settings', [\App\Http\Controllers\Admin\AffiliateSettingsController::class, 'index'])->name('affiliate.settings.index');
    Route::put('affiliate/settings', [\App\Http\Controllers\Admin\AffiliateSettingsController::class, 'update'])->name('affiliate.settings.update');

    // Reports
    Route::get('reports/tax', [DashboardController::class, 'taxReport'])->name('reports.tax');
    Route::get('reports/payments', [DashboardController::class, 'paymentReport'])->name('reports.payments');
    Route::get('reports/revenue', [DashboardController::class, 'revenueReport'])->name('reports.revenue');

    // Phase 1 Features Management

    // Bank Accounts (across all businesses)
    Route::get('bank-accounts', [BankAccountController::class, 'index'])->name('bank-accounts.index');
    Route::get('bank-accounts/{bankAccount}', [BankAccountController::class, 'show'])->name('bank-accounts.show');
    Route::post('bank-accounts/{bankAccount}/deactivate', [BankAccountController::class, 'deactivate'])->name('bank-accounts.deactivate');
    Route::post('bank-accounts/{bankAccount}/activate', [BankAccountController::class, 'activate'])->name('bank-accounts.activate');

    // Transactions (across all businesses)
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
    Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

    // Compliance Deadlines (across all businesses)
    Route::get('compliance', [ComplianceController::class, 'index'])->name('compliance.index');
    Route::get('compliance/reports/overdue', [ComplianceController::class, 'overdueReport'])->name('compliance.overdue-report');
    Route::get('compliance/{deadline}', [ComplianceController::class, 'show'])->name('compliance.show');

    // VAT Returns (across all businesses)
    Route::get('vat-returns', [VatController::class, 'index'])->name('vat-returns.index');
    Route::get('vat-returns/export', [VatController::class, 'export'])->name('vat-returns.export');
    // VAT Form 002 export (CSV or XML)
    Route::get('vat-returns/export/form002', [VatController::class, 'exportForm002'])->name('vat-returns.export.form002');
    Route::get('vat-returns/{return}/export/form002', [VatController::class, 'exportForm002ForReturn'])->name('vat-returns.export.form002.single');
    Route::get('vat-returns/reports/revenue', [VatController::class, 'revenueReport'])->name('vat-returns.revenue-report');
    Route::get('vat-returns/{return}', [VatController::class, 'show'])->name('vat-returns.show');

    // PAYE Returns (across all businesses)
    Route::get('paye-returns', [PayeReturnController::class, 'index'])->name('paye-returns.index');
    Route::get('paye-returns/export', [PayeReturnController::class, 'export'])->name('paye-returns.export');
    // PAYE schedules export (per return)
    Route::get('paye-returns/{payeReturn}/export-schedules', [PayeReturnController::class, 'exportSchedules'])->name('paye-returns.export.schedules');
    Route::get('paye-returns/export/schedules', [PayeReturnController::class, 'exportSchedulesBulk'])->name('paye-returns.export.schedules.bulk');
    Route::get('paye-returns/reports/revenue', [PayeReturnController::class, 'revenueReport'])->name('paye-returns.revenue-report');
    Route::get('paye-returns/{payeReturn}', [PayeReturnController::class, 'show'])->name('paye-returns.show');

    // WHT Returns (across all businesses)
    Route::get('wht-returns/returns/export', [WhtReturnController::class, 'exportReturns'])->name('wht-returns.export-returns');
    Route::get('wht-returns/returns/{whtReturn}', [WhtReturnController::class, 'showReturn'])->name('wht-returns.show-return');
    Route::get('wht-returns/returns', [WhtReturnController::class, 'returns'])->name('wht-returns.returns');
    Route::get('wht-returns/export', [WhtReturnController::class, 'exportTransactions'])->name('wht-returns.export-transactions');
    Route::get('wht-returns/reports/revenue', [WhtReturnController::class, 'revenueReport'])->name('wht-returns.revenue-report');
    Route::get('wht-returns/{whtTransaction}', [WhtReturnController::class, 'showTransaction'])->name('wht-returns.show-transaction');
    Route::get('wht-returns', [WhtReturnController::class, 'index'])->name('wht-returns.index');

    // AI Automation Management
    Route::get('ai-automation', [AiAutomationController::class, 'index'])->name('ai-automation.index');
    Route::get('ai-automation/{aiSuggestion}', [AiAutomationController::class, 'show'])->name('ai-automation.show');
    Route::post('ai-automation/{aiSuggestion}/apply', [AiAutomationController::class, 'apply'])->name('ai-automation.apply');
    Route::post('ai-automation/{aiSuggestion}/dismiss', [AiAutomationController::class, 'dismiss'])->name('ai-automation.dismiss');
    Route::post('ai-automation/{aiSuggestion}/feedback', [AiAutomationController::class, 'feedback'])->name('ai-automation.feedback');

    // Blog CRUD (Inertia pages + form actions)
    Route::prefix('blog')->name('blog.')->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name('index');
        Route::get('/create', [BlogController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [BlogController::class, 'edit'])->name('edit');

        // Form actions (store, update, delete)
        Route::post('/', [BlogController::class, 'store'])->name('store');
        Route::put('/{id}', [BlogController::class, 'update'])->name('update');
        Route::delete('/{id}', [BlogController::class, 'destroy'])->name('destroy');
    });

    // Broadcast emails
    Route::get('broadcast', [BroadcastEmailController::class, 'create'])->name('broadcast.create');
    Route::post('broadcast', [BroadcastEmailController::class, 'send'])->name('broadcast.send');

    // Error Logs
    Route::prefix('error-logs')->name('error-logs.')->controller(\App\Http\Controllers\Admin\ErrorLogController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{errorLog}', 'show')->name('show');
        Route::post('/{errorLog}/resolve', 'resolve')->name('resolve');
        Route::post('/bulk-resolve', 'bulkResolve')->name('bulk-resolve');
        Route::delete('/{errorLog}', 'destroy')->name('destroy');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        Route::post('/clear-resolved', 'clearResolved')->name('clear-resolved');
    });
});
