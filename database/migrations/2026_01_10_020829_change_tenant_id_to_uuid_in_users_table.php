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
        // Drop any existing foreign keys on tenant_id column
        $existingConstraints = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
            AND TABLE_NAME = 'users'
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND CONSTRAINT_NAME LIKE '%tenant_id%'
        ");

        foreach ($existingConstraints as $constraint) {
            DB::statement("ALTER TABLE users DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable()->change();
            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop any existing foreign keys on tenant_id column
        $existingConstraints = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
            AND TABLE_NAME = 'users'
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND CONSTRAINT_NAME LIKE '%tenant_id%'
        ");

        foreach ($existingConstraints as $constraint) {
            DB::statement("ALTER TABLE users DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
        }

        Schema::table('users', function (Blueprint $table) {
            $table->char('tenant_id', 36)->nullable()->change();
            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }
};
