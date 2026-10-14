<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourierDashboardController;
use App\Http\Controllers\CourierIncomingController;
use App\Http\Controllers\VendorDashboardController;
use App\Http\Controllers\VendorMonitorController;
use App\Http\Controllers\VendorPackagesController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::middleware(['auth:sanctum', 'throttle:vendor'])->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/courier/dashboard', [CourierDashboardController::class, 'show']);
        Route::get('/courier/tasks/{awb}', [CourierIncomingController::class, 'show']);
        Route::post('/courier/tasks/{awb}/accept', [CourierIncomingController::class, 'accept']);
        Route::post('/courier/tasks/{awb}/reject', [CourierIncomingController::class, 'reject']);
        Route::get('/vendor/packages', [VendorPackagesController::class, 'index']);
        Route::get('/vendor/packages/export', [VendorPackagesController::class, 'export']);
        Route::get('/vendor/dashboard', [VendorDashboardController::class, 'show']);
        Route::put('/vendor/shipments/{awb}/readiness', [VendorDashboardController::class, 'readiness']);
        Route::get('/vendor/shipments/{awb}', [VendorMonitorController::class, 'show']);
        Route::get('/vendor/shipments/{awb}/telemetry', [VendorMonitorController::class, 'telemetry'])->name('vendor.telemetry');
        Route::get('/vendor/shipments/{awb}/events', [VendorMonitorController::class, 'events'])->name('vendor.events');
        Route::get('/vendor/shipments/{awb}/issues', [VendorMonitorController::class, 'issues']);
        Route::get('/vendor/shipments/{awb}/reports', [VendorMonitorController::class, 'reports'])->name('vendor.reports');
        Route::get('/vendor/shipments/{awb}/inspection-photo', [VendorMonitorController::class, 'inspectionPhoto']);
        Route::get('/vendor/reports/{report}/download', [VendorMonitorController::class, 'download'])->whereNumber('report')->name('vendor.report.download');
        Route::get('/vendor/reports/{report}/verify', [VendorMonitorController::class, 'verify'])->whereNumber('report')->name('vendor.report.verify');
    });
});
