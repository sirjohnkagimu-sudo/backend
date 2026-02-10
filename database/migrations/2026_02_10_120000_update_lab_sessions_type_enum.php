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
        // MySQL doesn't support modifying ENUMs directly, so we need to use raw SQL
        DB::statement("ALTER TABLE lab_sessions MODIFY COLUMN type ENUM('class', 'exam', 'practical', 'practical_exam', 'maintenance', 'other')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE lab_sessions MODIFY COLUMN type ENUM('class', 'exam', 'practical', 'maintenance', 'other')");
    }
};
