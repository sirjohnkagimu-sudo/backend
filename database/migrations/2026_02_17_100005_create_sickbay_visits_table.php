<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSickbayVisitsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sickbay_visits', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('visited_by')->nullable();
            $table->dateTime('visit_date');
            $table->string('visit_type', 50); // checkup, illness, injury, follow-up, vaccination
            $table->text('symptoms')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->string('temperature', 20)->nullable();
            $table->string('blood_pressure', 30)->nullable();
            $table->integer('pulse')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->json('medicines_given')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 50)->default('completed'); // completed, referred, admitted
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('sickbay_students')->onDelete('cascade');
            $table->foreign('visited_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['tenant_id', 'visit_date']);
            $table->index(['tenant_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sickbay_visits');
    }
}
