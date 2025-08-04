<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes for better query performance
        
        // Categories table indexes
        Schema::table('categories', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order'], 'categories_active_sort_index');
            $table->index('slug', 'categories_slug_index');
        });

        // Menus table indexes
        Schema::table('menus', function (Blueprint $table) {
            $table->index(['category_id', 'is_available'], 'menus_category_available_index');
            $table->index(['is_available', 'sort_order'], 'menus_available_sort_index');
            $table->index('price', 'menus_price_index');
        });

        // Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'orders_status_created_index');
            $table->index(['payment_status', 'created_at'], 'orders_payment_created_index');
            $table->index('table_number', 'orders_table_index');
            $table->index('customer_phone', 'orders_customer_phone_index');
            $table->index('created_at', 'orders_created_at_index');
        });

        // Order items table indexes
        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['order_id', 'menu_id'], 'order_items_order_menu_index');
            $table->index('menu_id', 'order_items_menu_index');
        });

        // Payments table indexes
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['order_id', 'status'], 'payments_order_status_index');
            $table->index(['status', 'processed_at'], 'payments_status_processed_index');
            $table->index('method', 'payments_method_index');
            $table->index('processed_by', 'payments_processed_by_index');
            $table->index('processed_at', 'payments_processed_at_index');
        });

        // Users table indexes (if not already present)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasIndex('users', 'users_email_verified_at_index')) {
                $table->index('email_verified_at', 'users_email_verified_at_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes
        
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_active_sort_index');
            $table->dropIndex('categories_slug_index');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex('menus_category_available_index');
            $table->dropIndex('menus_available_sort_index');
            $table->dropIndex('menus_price_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_created_index');
            $table->dropIndex('orders_payment_created_index');
            $table->dropIndex('orders_table_index');
            $table->dropIndex('orders_customer_phone_index');
            $table->dropIndex('orders_created_at_index');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_order_menu_index');
            $table->dropIndex('order_items_menu_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_order_status_index');
            $table->dropIndex('payments_status_processed_index');
            $table->dropIndex('payments_method_index');
            $table->dropIndex('payments_processed_by_index');
            $table->dropIndex('payments_processed_at_index');
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasIndex('users', 'users_email_verified_at_index')) {
                $table->dropIndex('users_email_verified_at_index');
            }
        });
    }
};
