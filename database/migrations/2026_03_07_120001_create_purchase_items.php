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
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('product_name');
            $table->string('slug')->nullable();
            $table->string('sku')->nullable();
            $table->unsignedBigInteger('product_category_id')->nullable();
            $table->unsignedBigInteger('product_brand_id')->nullable();
            $table->decimal('buying_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->decimal('variation_price', 10, 2)->nullable();
            $table->boolean('status')->default(true);
            $table->integer('order')->nullable(); // Assuming this is sort order
            $table->integer('product_quantity')->nullable(); // Renamed to avoid conflict with item quantity
            $table->boolean('show_stock_out')->default(false);
            $table->integer('maximum_purchase_quantity')->nullable();
            $table->integer('low_stock_quantity_warning')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->boolean('refundable')->default(false);
            $table->text('description')->nullable();
            $table->text('shipping_and_return')->nullable();
            $table->boolean('add_to_flash_sale')->default(false);
            $table->decimal('discount', 5, 2)->nullable();
            $table->date('offer_start_date')->nullable();
            $table->date('offer_end_date')->nullable();
            $table->string('shipping_type')->nullable();
            $table->decimal('shipping_cost', 10, 2)->nullable();
            $table->boolean('is_product_quantity_multiply')->default(false);
            $table->string('barcode')->nullable();
            $table->timestamps();

            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
