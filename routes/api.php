<?php

use Illuminate\Support\Facades\Route;

// Admin routes (protected by Sanctum)
Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {
    
    // App management
    Route::prefix('apps')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AppController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Admin\AppController::class, 'store']);
        Route::get('/{app}', [\App\Http\Controllers\Admin\AppController::class, 'show']);
        Route::put('/{app}', [\App\Http\Controllers\Admin\AppController::class, 'update']);
        Route::delete('/{app}', [\App\Http\Controllers\Admin\AppController::class, 'destroy']);
        Route::post('/{app}/regenerate-token', [\App\Http\Controllers\Admin\AppController::class, 'regenerateToken']);
    });
    
    // Payment management
    Route::prefix('payments')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PaymentController::class, 'index']);
        Route::get('/{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'show']);
    });
    
    // Dashboard
    Route::get('/dashboard/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'stats']);
});

// App API routes (protected by app token)
Route::prefix('v1')->middleware(['auth.app'])->group(function () {
    
    // Payment operations
    Route::prefix('payments')->group(function () {
        Route::post('/create', [\App\Http\Controllers\Api\PaymentController::class, 'create']);
        Route::get('/{externalId}', [\App\Http\Controllers\Api\PaymentController::class, 'show']);
        Route::get('/', [\App\Http\Controllers\Api\PaymentController::class, 'index']);
        Route::post('/{externalId}/cancel', [\App\Http\Controllers\Api\PaymentController::class, 'cancel']);
        Route::get('/{externalId}/continue', [\App\Http\Controllers\Api\PaymentController::class, 'continue']);
    });
});

// Webhooks (no auth, validated by Midtrans signature)
Route::post('/webhooks/midtrans', [\App\Http\Controllers\WebhookController::class, 'midtrans']);
