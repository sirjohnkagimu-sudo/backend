<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSickbayStudentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sickbay_students', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->string('admission_number', 50)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('gender', 20);
            $table->date('date_of_birth');
            $table->string('class', 50)->nullable();
            $table->string('stream', 50)->nullable();
            $table->string('parent_name', 200)->nullable();
            $table->string('parent_phone', 50)->nullable();
            $table->string('parent_email', 200)->nullable();
            $table->string('blood_type', 10)->nullable();
            $table->text('allergies')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->text('emergency_contact')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sickbay_students');
    }
}
