<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lab_sessions', function (Blueprint $table) {
             $table->id();
            $table->uuid('school_id');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users');

            $table->string('title');
            $table->enum('type', ['class', 'exam', 'practical', 'maintenance', 'other']);
            $table->enum('lab_type', ['chemistry', 'physics', 'biology', 'agriculture', 'general']);

            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            $table->dateTime('start_date');
            $table->dateTime('end_date');

            $table->string('start_time'); // for frontend convenience
            $table->string('end_time');

            $table->unsignedInteger('students')->default(0);
            $table->string('instructor')->nullable();

            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_sessions');
    }
};
