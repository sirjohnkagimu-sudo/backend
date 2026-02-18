<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSickbayAdmissionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sickbay_admissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('visit_id')->nullable();
            $table->unsignedBigInteger('admitted_by')->nullable();
            $table->dateTime('admission_date');
            $table->dateTime('discharge_date')->nullable();
            $table->string('bed_number', 20)->nullable();
            $table->string('ward', 100)->nullable();
            $table->text('reason')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('daily_notes')->nullable();
            $table->string('status', 50)->default('admitted'); // admitted, discharged, transferred
            $table->text('discharge_notes')->nullable();
            $table->text('follow_up_instructions')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('sickbay_students')->onDelete('cascade');
            $table->foreign('visit_id')->references('id')->on('sickbay_visits')->onDelete('set null');
            $table->foreign('admitted_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'admission_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sickbay_admissions');
    }
}
