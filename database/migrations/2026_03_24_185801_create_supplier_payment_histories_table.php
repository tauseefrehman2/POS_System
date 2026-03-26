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
        Schema::create('supplier_payment_histories', function (Blueprint $table) {
            $table->id();

            $table->dateTime('date');

            $table->string('payment_name'); // purchase, refund, supplier_payment etc

            $table->foreignId('supplier_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->decimal('credit', 12, 2)->default(0); // money received
            $table->decimal('debit', 12, 2)->default(0);  // money given
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_payment_histories');
    }
};
