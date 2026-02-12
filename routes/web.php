<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\WebDashboardController;
use App\Http\Controllers\Admin\WebAppController;
use App\Http\Controllers\Admin\WebPaymentController;
use App\Http\Controllers\Admin\ProfileController;
use Illuminate\Support\Facades\Route;

// Root route - redirect to dashboard if authenticated, login if not
Route::get('/', function () {
    return auth()->check() ? redirect('/admin/dashboard') : redirect('/login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin routes (web interface)

// Snap redirect handlers for test payments (public, no auth, no /admin prefix)
Route::get('/test-payment/success', [\App\Http\Controllers\Admin\TestPaymentController::class, 'snapSuccess'])->name('test-payment.snap-success');
Route::get('/test-payment/failure', [\App\Http\Controllers\Admin\TestPaymentController::class, 'snapFailure'])->name('test-payment.snap-failure');
Route::get('/test-payment/unfinish', [\App\Http\Controllers\Admin\TestPaymentController::class, 'snapUnfinish'])->name('test-payment.snap-unfinish');

Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [WebDashboardController::class, 'index'])->name('dashboard');
    // Apps management
    Route::resource('apps', WebAppController::class);
    Route::post('/apps/{app}/regenerate-token', [WebAppController::class, 'regenerateToken'])->name('apps.regenerate-token');
    // Payments management
    Route::resource('payments', WebPaymentController::class)->only(['index', 'show']);
    // Test payments
    Route::get('/test-payment', [\App\Http\Controllers\Admin\TestPaymentController::class, 'create'])->name('test-payment');
    Route::post('/test-payment', [\App\Http\Controllers\Admin\TestPaymentController::class, 'store'])->name('test-payment.store');
    Route::post('/payments/{payment}/mark-paid', [\App\Http\Controllers\Admin\TestPaymentController::class, 'markAsPaid'])->name('payments.mark-paid');
    Route::post('/payments/{payment}/mark-expired', [\App\Http\Controllers\Admin\TestPaymentController::class, 'markAsExpired'])->name('payments.mark-expired');
    // Profile
    Route::get('/profile/change-password', [ProfileController::class, 'showChangePasswordForm'])->name('profile.change-password');
    Route::put('/profile/change-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
});
