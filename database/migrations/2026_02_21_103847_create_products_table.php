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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories');
            $table->foreignId('product_brand_id')->nullable()->constrained('product_brands');
            $table->decimal('buying_price', 13, 6)->default(0);
            $table->decimal('selling_price', 13, 6)->default(0);
            $table->decimal('variation_price', 13, 6)->default(0);
            $table->tinyInteger('status')->default(1);
            $table->bigInteger('order')->default(1);
            $table->tinyInteger('can_purchasable')->default(1);
            $table->tinyInteger('show_stock_out')->default(1);
            $table->unsignedBigInteger('maximum_purchase_quantity')->default(1);
            $table->unsignedBigInteger('low_stock_quantity_warning')->default(1);
            $table->string('weight')->nullable();
            $table->tinyInteger('refundable')->default(1);
            $table->longText('description')->nullable();
            $table->longText('shipping_and_return')->nullable();
            $table->unsignedTinyInteger('add_to_flash_sale')->default(0);
            $table->decimal('discount', 13, 6)->default(0);
            $table->dateTime('offer_start_date')->nullable();
            $table->dateTime('offer_end_date')->nullable();
            $table->tinyInteger('shipping_type')->default(1);
            $table->decimal('shipping_cost', 13, 6)->default(0);
            $table->tinyInteger('is_product_quantity_multiply')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
