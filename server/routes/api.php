<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VendorDashboardController;
use App\Http\Controllers\VendorPackagesController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::middleware(['auth:sanctum', 'throttle:vendor'])->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/vendor/packages', [VendorPackagesController::class, 'index']);
        Route::get('/vendor/packages/export', [VendorPackagesController::class, 'export']);
        Route::get('/vendor/dashboard', [VendorDashboardController::class, 'show']);
        Route::put('/vendor/shipments/{awb}/readiness', [VendorDashboardController::class, 'readiness']);
    });
});
