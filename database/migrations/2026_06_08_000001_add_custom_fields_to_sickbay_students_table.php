<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sickbay_students', function (Blueprint $table) {
            $table->json('custom_fields')->nullable()->after('emergency_contact');
        });
    }

    public function down(): void
    {
        Schema::table('sickbay_students', function (Blueprint $table) {
            $table->dropColumn('custom_fields');
        });
    }
};
