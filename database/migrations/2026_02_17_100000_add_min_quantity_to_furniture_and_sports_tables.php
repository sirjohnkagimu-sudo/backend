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
        // Add min_quantity column to furniture table if it doesn't exist
        if (Schema::hasTable('furniture') && !Schema::hasColumn('furniture', 'min_quantity')) {
            Schema::table('furniture', function (Blueprint $table) {
                $table->integer('min_quantity')->default(5)->nullable()->after('in_stock');
            });
        }

        // Add min_quantity column to sports table if it doesn't exist
        if (Schema::hasTable('sports') && !Schema::hasColumn('sports', 'min_quantity')) {
            Schema::table('sports', function (Blueprint $table) {
                $table->integer('min_quantity')->default(5)->nullable()->after('in_stock');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('furniture', function (Blueprint $table) {
            if (Schema::hasColumn('furniture', 'min_quantity')) {
                $table->dropColumn('min_quantity');
            }
        });

        Schema::table('sports', function (Blueprint $table) {
            if (Schema::hasColumn('sports', 'min_quantity')) {
                $table->dropColumn('min_quantity');
            }
        });
    }
};
