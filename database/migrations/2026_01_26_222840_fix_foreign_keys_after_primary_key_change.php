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
        // Drop existing foreign keys that reference schools.id
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('lab_sessions', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        // Recreate foreign keys to reference schools.centre_number
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('centre_number')->on('schools')->onDelete('set null');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('centre_number')->on('schools')->onDelete('cascade');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('centre_number')->on('schools')->onDelete('cascade');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('centre_number')->on('schools')->onDelete('cascade');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('centre_number')->on('schools')->onDelete('cascade');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('centre_number')->on('schools')->onDelete('cascade');
        });

        Schema::table('lab_sessions', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('centre_number')->on('schools')->onDelete('cascade');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('centre_number')->on('schools')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the updated foreign keys
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('lab_sessions', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        // Recreate foreign keys to reference schools.id (assuming id exists in rollback)
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('set null');
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

        Schema::table('lab_sessions', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }
};
