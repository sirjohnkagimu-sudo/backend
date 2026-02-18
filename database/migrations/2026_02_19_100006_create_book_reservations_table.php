<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_reservations', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('requester_id')->nullable();
            $table->string('requester_type', 50)->nullable(); // teacher, student, staff
            $table->string('requester_name', 200)->nullable();
            $table->string('subject', 100)->nullable();
            $table->string('topic', 255)->nullable();
            $table->unsignedBigInteger('book_title_id')->nullable();
            $table->string('class_name', 50)->nullable();
            $table->integer('number_of_copies')->default(1);
            $table->enum('purpose', ['lesson', 'research', 'exam', 'reading'])->default('reading');
            $table->date('requested_date');
            $table->date('required_date');
            $table->enum('status', ['pending', 'approved', 'rejected', 'fulfilled', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->date('approved_date')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->date('fulfilled_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('book_title_id')->references('id')->on('book_titles')->onDelete('set null');
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_reservations');
    }
};
