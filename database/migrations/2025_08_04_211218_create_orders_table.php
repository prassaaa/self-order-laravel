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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('table_number')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'])->default('pending');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['status', 'created_at']);
            $table->index(['payment_status', 'created_at']);
            $table->index('order_number');
            $table->index('table_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
