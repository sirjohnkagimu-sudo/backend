<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_clearances', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('student_id');
            $table->string('student_name', 200)->nullable();
            $table->string('student_class', 50)->nullable();
            $table->enum('term', ['Term 1', 'Term 2', 'Term 3'])->default('Term 1');
            $table->string('academic_year', 20)->nullable();
            $table->enum('status', ['cleared', 'pending', 'blocked', 'incomplete'])->default('pending');
            $table->json('borrowed_books')->nullable();
            $table->integer('total_borrowed')->default(0);
            $table->integer('total_returned')->default(0);
            $table->integer('total_outstanding')->default(0);
            $table->decimal('total_lost_book_fees', 12, 2)->default(0);
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->decimal('total_balance', 12, 2)->default(0);
            $table->unsignedBigInteger('cleared_by')->nullable();
            $table->date('cleared_date')->nullable();
            $table->text('blockage_reason')->nullable();
            $table->boolean('report_card_blocked')->default(false);
            $table->text('qr_code')->nullable();
            $table->text('signature')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('library_students')->onDelete('cascade');
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_clearances');
    }
};
