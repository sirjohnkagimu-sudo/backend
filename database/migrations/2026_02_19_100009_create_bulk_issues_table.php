<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_issues', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('book_title_id');
            $table->string('class', 50)->nullable();
            $table->enum('term', ['Term 1', 'Term 2', 'Term 3'])->default('Term 1');
            $table->string('academic_year', 20)->nullable();
            $table->date('issue_date');
            $table->date('due_date');
            $table->unsignedBigInteger('issued_by');
            $table->enum('status', ['pending', 'processing', 'completed'])->default('pending');
            $table->integer('total_copies')->default(0);
            $table->integer('issued_copies')->default(0);
            $table->integer('pending_copies')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('book_title_id')->references('id')->on('book_titles')->onDelete('cascade');
            $table->foreign('issued_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_issues');
    }
};
