<?php

use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\MenuController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public API routes (for customer interface)
Route::prefix('v1')->group(function () {
    // Categories - Public access
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    
    // Menus - Public access
    Route::get('menus', [MenuController::class, 'index']);
    Route::get('menus/{menu}', [MenuController::class, 'show']);
    Route::get('categories/{category}/menus', [MenuController::class, 'byCategory']);
    
    // Orders - Public creation, auth required for management
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
});

// Protected API routes (require authentication)
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    
    // Admin only routes
    Route::middleware(['role:admin'])->group(function () {
        // Categories management
        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
        
        // Menus management
        Route::post('menus', [MenuController::class, 'store']);
        Route::put('menus/{menu}', [MenuController::class, 'update']);
        Route::delete('menus/{menu}', [MenuController::class, 'destroy']);
        
        // Reports
        Route::get('reports/sales', [ReportController::class, 'sales']);
        Route::get('reports/popular-items', [ReportController::class, 'popularItems']);
        Route::get('reports/revenue', [ReportController::class, 'revenue']);
        Route::get('reports/export', [ReportController::class, 'export']);
    });
    
    // Admin and Kasir routes
    Route::middleware(['role:admin|kasir'])->group(function () {
        // Orders management
        Route::get('orders', [OrderController::class, 'index']);
        Route::put('orders/{order}', [OrderController::class, 'update']);
        Route::put('orders/{order}/status', [OrderController::class, 'updateStatus']);
        Route::delete('orders/{order}', [OrderController::class, 'destroy']);
        
        // Payments
        Route::get('payments', [PaymentController::class, 'index']);
        Route::post('payments', [PaymentController::class, 'store']);
        Route::put('payments/{payment}', [PaymentController::class, 'update']);
        Route::get('payments/{payment}', [PaymentController::class, 'show']);
        
        // Order specific payments
        Route::get('orders/{order}/payments', [PaymentController::class, 'orderPayments']);
        Route::post('orders/{order}/payments', [PaymentController::class, 'processPayment']);
    });
});
