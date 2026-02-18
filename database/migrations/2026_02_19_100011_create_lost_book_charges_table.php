<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lost_book_charges', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('copy_id')->nullable();
            $table->string('book_title', 255)->nullable();
            $table->decimal('replacement_cost', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'waived'])->default('pending');
            $table->string('invoice_number', 100)->nullable();
            $table->date('created_date');
            $table->date('paid_date')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('receipt_number', 100)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('library_students')->onDelete('cascade');
            $table->foreign('copy_id')->references('id')->on('book_copies')->onDelete('set null');
            $table->index(['tenant_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lost_book_charges');
    }
};
