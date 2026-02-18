<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('student_id');
            $table->string('student_name', 200)->nullable();
            $table->enum('type', ['lost-book', 'damaged-book', 'fine', 'other'])->default('fine');
            $table->string('reference_type', 50)->nullable(); // charge, invoice
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('payment_method', 50)->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->string('receipt_number', 100)->nullable();
            $table->unsignedBigInteger('collected_by');
            $table->date('collection_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('library_students')->onDelete('cascade');
            $table->foreign('collected_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_payments');
    }
};
