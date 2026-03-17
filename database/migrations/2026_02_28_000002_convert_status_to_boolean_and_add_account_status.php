<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert products status to boolean
        Schema::table('products', function (Blueprint $table) {
            // SQLite doesn't support changing column type, so we need to use raw SQL for MySQL/PostgreSQL
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement('ALTER TABLE products MODIFY COLUMN status BOOLEAN DEFAULT true');
            }
        });

        // Convert product_brands status to boolean
        Schema::table('product_brands', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement('ALTER TABLE product_brands MODIFY COLUMN status BOOLEAN DEFAULT true');
            }
        });

        // Convert product_categories status to boolean
        Schema::table('product_categories', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement('ALTER TABLE product_categories MODIFY COLUMN status BOOLEAN DEFAULT true');
            }
        });

        // Convert users status to boolean and add account_status enum
        Schema::table('users', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement('ALTER TABLE users MODIFY COLUMN status BOOLEAN DEFAULT true');
            }
            $table->enum('account_status', ['active', 'inactive'])->default('active')->after('status');
        });

        // Update order_items status to boolean
        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'status')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (DB::getDriverName() !== 'sqlite') {
                    DB::statement('ALTER TABLE order_items MODIFY COLUMN status BOOLEAN DEFAULT true');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert order_items status back to tinyInteger
        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'status')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (DB::getDriverName() !== 'sqlite') {
                    DB::statement('ALTER TABLE order_items MODIFY COLUMN status TINYINT DEFAULT 1');
                }
            });
        }

        // Revert users back
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('account_status');
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement('ALTER TABLE users MODIFY COLUMN status TINYINT UNSIGNED DEFAULT 1');
            }
        });

        // Revert product_categories status back
        Schema::table('product_categories', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement('ALTER TABLE product_categories MODIFY COLUMN status TINYINT DEFAULT 1');
            }
        });

        // Revert product_brands status back
        Schema::table('product_brands', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement('ALTER TABLE product_brands MODIFY COLUMN status TINYINT DEFAULT 1');
            }
        });

        // Revert products status back
        Schema::table('products', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement('ALTER TABLE products MODIFY COLUMN status TINYINT DEFAULT 1');
            }
        });
    }
};
