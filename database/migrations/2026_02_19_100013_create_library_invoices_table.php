<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->string('invoice_number', 100)->unique();
            $table->unsignedBigInteger('student_id');
            $table->string('student_name', 200)->nullable();
            $table->string('student_class', 50)->nullable();
            $table->json('items')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->enum('status', ['pending', 'partial', 'paid', 'cancelled'])->default('pending');
            $table->date('created_date');
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->date('cancelled_date')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('library_students')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_invoices');
    }
};
