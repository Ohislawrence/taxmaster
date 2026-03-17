<?php

use App\Http\Controllers\Accountant\DashboardController;
use App\Http\Controllers\Accountant\BusinessController as AccountantBusinessController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified', \Spatie\Permission\Middleware\RoleMiddleware::class . ':accountant'])->prefix('accountant')->name('accountant.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Accountant-managed businesses

    // Accountant-managed businesses
    Route::get('businesses', [AccountantBusinessController::class, 'index'])->name('businesses.index');
    Route::get('businesses/create', [AccountantBusinessController::class, 'create'])->name('businesses.create');
    Route::post('businesses', [AccountantBusinessController::class, 'store'])->name('businesses.store');
    Route::get('businesses/{business}', [AccountantBusinessController::class, 'show'])->name('businesses.show');
    Route::get('businesses/{business}/invites', [\App\Http\Controllers\Accountant\BusinessInviteController::class, 'index'])->name('businesses.invites.index');
    Route::post('businesses/{business}/invite', [\App\Http\Controllers\Accountant\BusinessInviteController::class, 'store'])->name('businesses.invite');
    Route::post('businesses/{business}/detach', [AccountantBusinessController::class, 'detach'])->name('businesses.detach');
        // Affiliate dashboard for accountants
            Route::get('affiliate', [\App\Http\Controllers\Accountant\AffiliateController::class, 'index'])->name('affiliate.index');
            Route::post('affiliate/bank', [\App\Http\Controllers\Accountant\AffiliateController::class, 'updateBank'])->name('affiliate.bank.update');
});
