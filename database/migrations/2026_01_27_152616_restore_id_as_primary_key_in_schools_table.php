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
        // Drop the updated foreign keys if they exist
        $this->dropForeignIfExists('users', 'tenant_id');
        $this->dropForeignIfExists('categories', 'tenant_id');
        $this->dropForeignIfExists('suppliers', 'tenant_id');
        $this->dropForeignIfExists('locations', 'tenant_id');
        $this->dropForeignIfExists('items', 'tenant_id');
        $this->dropForeignIfExists('stock_movements', 'tenant_id');
        $this->dropForeignIfExists('lab_sessions', 'tenant_id');
        $this->dropForeignIfExists('transactions', 'tenant_id');
        $this->dropForeignIfExists('teacher_passcodes', 'school_id');
        $this->dropForeignIfExists('lab_access_codes', 'school_id');

        // Add id column if it doesn't exist
        Schema::table('schools', function (Blueprint $table) {
            if (!Schema::hasColumn('schools', 'id')) {
                $table->uuid('id');
            }
        });

        // Populate id with unique uuids
        DB::statement('UPDATE schools SET id = UUID()');

        // Set primary key only if it's not already set
        if (!$this->isPrimaryKeySet('schools', 'id')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->primary('id');
            });
        }

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

    private function dropForeignIfExists(string $table, string $column): void
    {
        $foreignKeyName = $table . '_' . $column . '_foreign';

        $exists = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND CONSTRAINT_NAME = ?
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$table, $foreignKeyName]);

        if (!empty($exists)) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->dropForeign([$column]);
            });
        }
    }

    private function isPrimaryKeySet(string $table, string $column): bool
    {
        $result = DB::select("
            SELECT COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE CONSTRAINT_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
            AND CONSTRAINT_NAME = 'PRIMARY'
        ", [$table, $column]);

        return !empty($result);
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
