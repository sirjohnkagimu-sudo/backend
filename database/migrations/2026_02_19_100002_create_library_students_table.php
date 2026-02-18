<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_students', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('student_id')->nullable(); // Links to sickbay_students or users
            $table->string('admission_number', 50)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('gender', 20)->nullable();
            $table->string('class', 50)->nullable();
            $table->string('stream', 50)->nullable();
            $table->string('library_card_number', 50)->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->string('emergency_contact', 200)->nullable();
            $table->enum('clearance_status', ['cleared', 'pending', 'blocked', 'incomplete'])->default('pending');
            $table->text('clearance_notes')->nullable();
            $table->integer('overdue_books')->default(0);
            $table->decimal('lost_book_balance', 12, 2)->default(0);
            $table->date('joined_date')->nullable();
            $table->date('last_visit')->nullable();
            $table->integer('total_visits')->default(0);
            $table->decimal('total_reading_hours', 8, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->index(['tenant_id', 'class']);
            $table->index(['tenant_id', 'clearance_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_students');
    }
};
