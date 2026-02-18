<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_allocations', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->string('teacher_name', 200)->nullable();
            $table->string('subject', 100)->nullable();
            $table->string('class_name', 50)->nullable();
            $table->unsignedBigInteger('book_title_id');
            $table->integer('copies_allocated')->default(0);
            $table->integer('copies_returned')->default(0);
            $table->date('allocation_date');
            $table->date('expected_return_date');
            $table->enum('status', ['active', 'partial', 'completed', 'overdue'])->default('active');
            $table->json('confirmations')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('book_title_id')->references('id')->on('book_titles')->onDelete('cascade');
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_allocations');
    }
};
