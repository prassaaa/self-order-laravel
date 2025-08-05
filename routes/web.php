<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Customer Interface Routes
Route::get('/', function () {
    return Inertia::render('customer/Home');
})->name('home');

Route::get('/menu', function () {
    return Inertia::render('customer/Menu');
})->name('menu');

Route::get('/about', function () {
    return Inertia::render('customer/About');
})->name('about');

Route::get('/checkout', function () {
    return Inertia::render('customer/Checkout');
})->name('checkout');

Route::get('/order/{order}', function ($orderId) {
    return Inertia::render('customer/OrderConfirmation', [
        'orderId' => (int) $orderId
    ]);
})->name('order.show');

// Dashboard dengan redirect berdasarkan role
Route::get('dashboard', function () {
    $user = auth()->user();

    // Jika user adalah admin, tampilkan admin dashboard
    if ($user->hasRole('admin')) {
        return Inertia::render('admin/Dashboard');
    }

    // Jika user adalah kasir, redirect ke kasir dashboard
    if ($user->hasRole('kasir')) {
        return redirect()->route('kasir.dashboard');
    }

    // Jika tidak memiliki role yang sesuai, logout
    auth()->logout();
    return redirect()->route('login')->with('error', 'Anda tidak memiliki akses ke sistem ini.');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'verified', 'role:admin,kasir'])->prefix('admin')->name('admin.')->group(function () {
    // Orders Management
    Route::get('/orders', function () {
        return Inertia::render('admin/Orders');
    })->name('orders.index');

    Route::get('/orders/{order}', function ($orderId) {
        return Inertia::render('admin/OrderDetail', [
            'orderId' => (int) $orderId
        ]);
    })->name('orders.show');

    // Menu Management
    Route::get('/menus', function () {
        return Inertia::render('admin/Menus');
    })->name('menus.index');

    Route::get('/menus/create', function () {
        return Inertia::render('admin/MenuForm');
    })->name('menus.create');

    Route::get('/menus/{menu}/edit', function ($menuId) {
        return Inertia::render('admin/MenuForm', [
            'menuId' => (int) $menuId
        ]);
    })->name('menus.edit');

    // Category Management
    Route::get('/categories', function () {
        return Inertia::render('admin/Categories');
    })->name('categories.index');

    Route::get('/categories/create', function () {
        return Inertia::render('admin/CategoryForm');
    })->name('categories.create');

    Route::get('/categories/{category}/edit', function ($categoryId) {
        return Inertia::render('admin/CategoryForm', [
            'categoryId' => (int) $categoryId
        ]);
    })->name('categories.edit');

    // Reports
    Route::get('/reports', function () {
        return Inertia::render('admin/Reports');
    })->name('reports.index');

    Route::get('/reports/popular', function () {
        return Inertia::render('admin/PopularItems');
    })->name('reports.popular');

    // User Management (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', function () {
            return Inertia::render('admin/Users');
        })->name('users.index');
    });

    // Settings
    Route::get('/settings', function () {
        return Inertia::render('admin/Settings');
    })->name('settings.index');
});

// Kasir Routes
Route::middleware(['auth', 'verified'])->prefix('kasir')->name('kasir.')->group(function () {
    // Kasir Dashboard
    Route::get('/dashboard', function () {
        // Manual role check untuk kasir
        $user = auth()->user();
        if (!$user->hasRole('kasir') && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized. Kasir access required.');
        }

        return Inertia::render('kasir/Dashboard');
    })->name('dashboard');

    // Orders Management
    Route::get('/orders', function () {
        return Inertia::render('kasir/Orders');
    })->name('orders');

    // Kitchen Display
    Route::get('/kitchen', function () {
        return Inertia::render('kasir/Kitchen');
    })->name('kitchen');

    // Payment Management
    Route::get('/payments', function () {
        return Inertia::render('kasir/Payments');
    })->name('payments');

    // Reports
    Route::get('/reports', function () {
        return Inertia::render('kasir/Reports');
    })->name('reports');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
