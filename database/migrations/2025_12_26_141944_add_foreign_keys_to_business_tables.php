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
        // Add foreign keys to business tables after schools table is created
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys in reverse order
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });
    }
};