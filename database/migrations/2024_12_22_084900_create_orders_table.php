<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_status_id')->constrained('order_statuses');
            $table->decimal('total_amount', 10, 2);
            $table->string('shipping_address');
            $table->string('payment_method')->nullable();
            $table->string('shipping_method')->nullable();
            $table->timestamp('order_date')->nullable();
            $table->decimal('bonus_amount', 10, 2)->nullable(); // Thêm trường bonus_amount
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};