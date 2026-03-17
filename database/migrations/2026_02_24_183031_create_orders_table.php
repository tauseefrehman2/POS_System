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
            $table->string('order_serial_no')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->decimal('subtotal', 13, 6)->default(0);
            $table->decimal('tax', 13, 6)->default(0);
            $table->decimal('discount', 13, 6)->default(0);
            $table->decimal('shipping_charge', 13, 6)->default(0);
            $table->decimal('total', 13, 6)->default(0);
            $table->tinyInteger('order_type')->default(1);
            $table->longText('special_instructions')->nullable();
            $table->string('payment_method')->nullable();
            $table->tinyInteger('payment_status')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('active')->default(1);
            $table->longText('reason')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();
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
