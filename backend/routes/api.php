<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'ok' => true,
        'service' => 'mini-tanomu-order-system-api',
    ]);
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

// Customer routes:
// GET /api/products
// GET /api/products/{id}
// POST /api/orders
// GET /api/orders
// GET /api/orders/{id}

// Admin routes:
// GET /api/admin/orders
// GET /api/admin/orders/{id}
// PATCH /api/admin/orders/{id}/status
// GET /api/admin/orders/export
