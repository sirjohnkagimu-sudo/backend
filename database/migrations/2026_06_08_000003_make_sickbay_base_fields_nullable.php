<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sickbay_students', function (Blueprint $table) {
            $table->string('admission_number', 50)->nullable()->change();
            $table->string('first_name', 100)->nullable()->change();
            $table->string('last_name', 100)->nullable()->change();
            $table->string('gender', 20)->nullable()->change();
            $table->string('class', 50)->nullable()->change();
            $table->string('stream', 50)->nullable()->change();
            $table->string('parent_name', 200)->nullable()->change();
            $table->string('parent_phone', 50)->nullable()->change();
            $table->string('parent_email', 200)->nullable()->change();
            $table->string('blood_type', 10)->nullable()->change();
            $table->text('allergies')->nullable()->change();
            $table->text('chronic_conditions')->nullable()->change();
            $table->text('emergency_contact')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sickbay_students', function (Blueprint $table) {
            $table->string('admission_number', 50)->nullable(false)->change();
            $table->string('first_name', 100)->nullable(false)->change();
            $table->string('last_name', 100)->nullable(false)->change();
            $table->string('gender', 20)->nullable(false)->change();
            $table->date('date_of_birth')->nullable(false)->change();
            $table->string('class', 50)->nullable(false)->change();
            $table->string('stream', 50)->nullable(false)->change();
            $table->string('parent_name', 200)->nullable(false)->change();
            $table->string('parent_phone', 50)->nullable(false)->change();
            $table->string('parent_email', 200)->nullable(false)->change();
            $table->string('blood_type', 10)->nullable(false)->change();
            $table->text('allergies')->nullable(false)->change();
            $table->text('chronic_conditions')->nullable(false)->change();
            $table->text('emergency_contact')->nullable(false)->change();
        });
    }
};
