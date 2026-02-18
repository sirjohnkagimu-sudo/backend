<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrow_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('copy_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('issued_by');
            $table->date('issued_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->enum('term', ['Term 1', 'Term 2', 'Term 3'])->default('Term 1');
            $table->string('academic_year', 20)->nullable();
            $table->enum('status', ['active', 'returned', 'overdue', 'lost', 'damaged'])->default('active');
            $table->enum('condition_on_return', ['new', 'good', 'torn', 'missing-pages', 'lost'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('copy_id')->references('id')->on('book_copies')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('library_students')->onDelete('cascade');
            $table->foreign('issued_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrow_transactions');
    }
};
