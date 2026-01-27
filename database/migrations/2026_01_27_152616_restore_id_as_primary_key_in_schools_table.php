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

        Schema::table('teacher_passcodes', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
        });

        Schema::table('lab_access_codes', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
        });

        // Add id column if it doesn't exist
        Schema::table('schools', function (Blueprint $table) {
            if (!Schema::hasColumn('schools', 'id')) {
                $table->uuid('id');
            }
        });

        // Populate id with unique uuids
        DB::statement('UPDATE schools SET id = UUID()');

        Schema::table('schools', function (Blueprint $table) {
            $table->primary('id');
        });

        // Recreate original foreign keys
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

        Schema::table('teacher_passcodes', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::table('lab_access_codes', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            //
        });
    }
};
