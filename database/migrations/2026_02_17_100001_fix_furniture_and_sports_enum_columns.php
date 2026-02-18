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
        // Fix furniture table - change enums to strings to allow more values
        Schema::table('furniture', function (Blueprint $table) {
            // Drop the enum constraints
            $table->string('category', 100)->change();
            $table->string('condition', 50)->change();
            $table->string('in_stock', 50)->change();
        });

        // Fix sports table - change condition to string
        if (Schema::hasTable('sports')) {
            Schema::table('sports', function (Blueprint $table) {
                if (Schema::hasColumn('sports', 'condition')) {
                    $table->string('condition', 50)->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert furniture table
        Schema::table('furniture', function (Blueprint $table) {
            $table->enum('category', ['office', 'classroom'])->change();
            $table->enum('condition', ['new', 'old'])->change();
            $table->string('in_stock')->change();
        });

        // Revert sports table
        if (Schema::hasTable('sports')) {
            Schema::table('sports', function (Blueprint $table) {
                if (Schema::hasColumn('sports', 'condition')) {
                    $table->enum('condition', ['new', 'old'])->change();
                }
            });
        }
    }
};
