<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'ok' => true,
        'service' => 'mini-tanomu-order-system-api',
    ]);
});

// Auth routes:
// POST /api/login
// POST /api/logout
// GET /api/me

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
