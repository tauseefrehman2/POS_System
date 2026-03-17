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
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('phone')->nullable()->after('email');
            $table->string('username')->nullable()->after('phone');
            $table->string('device_token')->nullable()->after('password');
            $table->string('web_token')->nullable()->after('device_token');
            $table->unsignedTinyInteger('status')->default(1)->after('web_token');
            $table->string('country_code')->nullable()->after('status');
            $table->unsignedTinyInteger('is_guest')->default(0)->after('country_code');
            $table->decimal('balance', 13, 6)->default(0)->after('is_guest');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['balance', 'is_guest', 'country_code', 'status', 'web_token', 'device_token', 'username', 'phone']);
            $table->string('email')->unique()->change();
        });
    }
};
