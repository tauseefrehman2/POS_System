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
        Schema::table('product_brands', function (Blueprint $table) {
            $table->string('remote_id')->nullable();
            $table->string('name_url')->nullable();
            $table->longText('description')->nullable();
            $table->tinyInteger('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_brands', function (Blueprint $table) {
            $table->dropColumn(['remote_id', 'name_url', 'description', 'status']);
        });
    }
};
