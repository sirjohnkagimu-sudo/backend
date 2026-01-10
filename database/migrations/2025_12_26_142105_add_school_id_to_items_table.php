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
        // Check if school_id column already exists
        $columnExists = DB::select("
            SELECT COLUMN_NAME
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'items'
            AND COLUMN_NAME = 'school_id'
        ");

        if (empty($columnExists)) {
            Schema::table('items', function (Blueprint $table) {
                $table->char('school_id', 36)->nullable();
                $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            });
        } else {
            // If column exists but no foreign key, add the foreign key
            $foreignKeyExists = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                AND TABLE_NAME = 'items'
                AND COLUMN_NAME = 'school_id'
                AND REFERENCED_TABLE_NAME = 'schools'
            ");

            if (empty($foreignKeyExists)) {
                Schema::table('items', function (Blueprint $table) {
                    $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};
