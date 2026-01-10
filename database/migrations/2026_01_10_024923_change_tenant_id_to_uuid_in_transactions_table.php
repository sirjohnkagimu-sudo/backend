<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1️⃣ Drop foreign key ONLY if it exists
        $foreignKey = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'transactions'
              AND COLUMN_NAME = 'tenant_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        Schema::table('transactions', function (Blueprint $table) use ($foreignKey) {
            if (!empty($foreignKey)) {
                $table->dropForeign($foreignKey[0]->CONSTRAINT_NAME);
            }
        });

        // 2️⃣ Drop column if exists
        if (Schema::hasColumn('transactions', 'tenant_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }

        // 3️⃣ Recreate as UUID
        Schema::table('transactions', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable()->after('id');
            $table->foreign('tenant_id')
                  ->references('id')
                  ->on('schools')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Reverse safely
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
            $table->uuid('tenant_id')->nullable();
        });
    }
};

