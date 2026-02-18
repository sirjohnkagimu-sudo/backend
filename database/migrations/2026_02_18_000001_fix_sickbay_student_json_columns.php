<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix allergies - convert plain strings to JSON arrays
        DB::statement("
            UPDATE sickbay_students
            SET allergies = JSON_ARRAY(allergies)
            WHERE allergies IS NOT NULL
            AND allergies != ''
            AND JSON_VALID(allergies) = 0
        ");

        // Fix chronic_conditions - convert plain strings to JSON arrays
        DB::statement("
            UPDATE sickbay_students
            SET chronic_conditions = JSON_ARRAY(chronic_conditions)
            WHERE chronic_conditions IS NOT NULL
            AND chronic_conditions != ''
            AND JSON_VALID(chronic_conditions) = 0
        ");

        // Fix emergency_contact - convert plain strings to JSON arrays
        DB::statement("
            UPDATE sickbay_students
            SET emergency_contact = JSON_ARRAY(emergency_contact)
            WHERE emergency_contact IS NOT NULL
            AND emergency_contact != ''
            AND JSON_VALID(emergency_contact) = 0
        ");
    }

    public function down(): void
    {
        // This is a one-way migration to fix data, no rollback needed
    }
};
