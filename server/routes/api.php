<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShipmentController;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Controllers\Api\ThermalInspectionController;
use App\Http\Controllers\Api\CourierController;
use App\Http\Controllers\Api\ThermalIncidentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CustomerShipmentController;

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'Anteraja Pharma API is running.',
        'data' => [
            'application' => 'Anteraja Pharma',
            'status' => 'healthy',
        ],
    ]);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ADMIN
    Route::middleware(EnsureUserHasRole::class . ':admin')->group(function () {
        // Shipments
        Route::get('/shipments', [ShipmentController::class, 'index']);
        Route::post('/shipments', [ShipmentController::class, 'store']);
        Route::get('/shipments/{id}', [ShipmentController::class, 'show']);
        Route::put('/shipments/{id}', [ShipmentController::class, 'update']);
        Route::get('/users/assignable', [UserController::class, 'assignable']);
        Route::get('/users/customers', [UserController::class, 'customers']);

        Route::post('/shipments/{id}/approve', [
            ShipmentController::class,
            'approve',
        ]);

        Route::post('/shipments/{id}/reject', [
            ShipmentController::class,
            'reject',
        ]);
        Route::post('/shipments/{id}/cancel', [
            ShipmentController::class,
            'cancel',
        ]);

        Route::post('/shipments/{id}/assign-checker', [
            ShipmentController::class,
            'assignChecker',
        ]);

        // Customers
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::post('/customers', [CustomerController::class, 'store']);
        Route::get('/customers/{id}', [CustomerController::class, 'show']);
        Route::put('/customers/{id}', [CustomerController::class, 'update']);
        Route::patch('/customers/{id}/status', [
            CustomerController::class,
            'updateStatus',
        ]);

        // Products
        Route::get('/products', [ProductController::class, 'index']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::get('/products/{id}', [ProductController::class, 'show']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::patch('/products/{id}/status', [
            ProductController::class,
            'updateStatus',
        ]);

        Route::post(
            '/shipments/{id}/assign-courier',
            [CourierController::class, 'assignCourier']
        );
        Route::get(
            '/thermal-incidents',
            [ThermalIncidentController::class, 'index']
        );

        Route::post(
            '/shipments/{id}/thermal-simulations',
            [ThermalIncidentController::class, 'simulate']
        );

        Route::patch(
            '/thermal-incidents/{id}/resolve',
            [ThermalIncidentController::class, 'resolve']
        );
        Route::get(
            '/thermal-incidents/{id}/debug',
            [ThermalIncidentController::class, 'debug']
        );
        Route::get('/reports/summary', [ReportController::class, 'summary']);
        Route::get('/reports/shipments', [ReportController::class, 'shipments']);

        Route::get(
            '/reports/shipments/{id}/pdf',
            [ReportController::class, 'shipmentPdf']
        );

        Route::get(
            '/reports/thermal-incidents/{id}/pdf',
            [ReportController::class, 'incidentPdf']
        );
    });

    // CHECKER
    Route::middleware(EnsureUserHasRole::class . ':checker')->group(function () {
        Route::get('/checker/shipments', [ShipmentController::class, 'checkerIndex']);
        Route::post(
            '/shipments/{id}/thermal-checks',
            [ThermalInspectionController::class, 'store']
        );
    });

    // COURIER
    Route::middleware(EnsureUserHasRole::class . ':courier')->group(function () {
        Route::get(
            '/courier/shipments',
            [CourierController::class, 'index']
        );

        Route::post(
            '/shipments/{id}/start',
            [CourierController::class, 'start']
        );

        Route::post(
            '/shipments/{id}/locations',
            [CourierController::class, 'updateLocation']
        );

        Route::post(
            '/shipments/{id}/delivery-proof',
            [CourierController::class, 'complete']
        );
    });

    // CUSTOMER
    Route::middleware(EnsureUserHasRole::class . ':customer')->group(function () {
        Route::get('/customer/shipments', [CustomerShipmentController::class, 'index']);
        Route::get('/customer/shipments/{id}', [CustomerShipmentController::class, 'show']);
    });
});