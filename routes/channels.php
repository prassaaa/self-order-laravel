<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Order updates channel - accessible by admin and kasir
Broadcast::channel('orders', function ($user) {
    return $user->hasAnyRole(['admin', 'kasir']);
});

// Kitchen notifications channel - accessible by admin and kasir
Broadcast::channel('kitchen', function ($user) {
    return $user->hasAnyRole(['admin', 'kasir']);
});

// Admin dashboard channel - accessible by admin only
Broadcast::channel('admin-dashboard', function ($user) {
    return $user->hasRole('admin');
});

// Customer order updates - accessible by the customer who placed the order
Broadcast::channel('customer-order.{orderId}', function ($user, $orderId) {
    // For now, allow all users to listen to order updates
    // In production, you might want to check if the user owns the order
    return true;
});
