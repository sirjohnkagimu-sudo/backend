<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSickbayReferralsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sickbay_referrals', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('visit_id')->nullable();
            $table->unsignedBigInteger('admission_id')->nullable();
            $table->unsignedBigInteger('referred_by')->nullable();
            $table->dateTime('referral_date');
            $table->string('facility_name', 200);
            $table->string('facility_contact', 50)->nullable();
            $table->string('facility_address', 500)->nullable();
            $table->string('department', 100)->nullable();
            $table->text('reason')->nullable();
            $table->text('clinical_notes')->nullable();
            $table->text('treatment_given')->nullable();
            $table->string('urgency', 50)->default('routine'); // emergency, urgent, routine
            $table->string('status', 50)->default('pending'); // pending, completed, cancelled
            $table->dateTime('follow_up_date')->nullable();
            $table->text('follow_up_notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('sickbay_students')->onDelete('cascade');
            $table->foreign('visit_id')->references('id')->on('sickbay_visits')->onDelete('set null');
            $table->foreign('admission_id')->references('id')->on('sickbay_admissions')->onDelete('set null');
            $table->foreign('referred_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['tenant_id', 'referral_date']);
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sickbay_referrals');
    }
}
