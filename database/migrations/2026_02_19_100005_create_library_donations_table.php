<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_donations', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('donor_id');
            $table->date('donation_date');
            $table->enum('type', ['books', 'cash', 'equipment', 'mixed'])->default('books');
            $table->json('book_copies')->nullable();
            $table->integer('total_books')->default(0);
            $table->decimal('total_value', 14, 2)->nullable();
            $table->enum('condition', ['new', 'good', 'mixed'])->default('good');
            $table->enum('purpose', ['general', 'specific-class', 'specific-subject', 'replacement'])->default('general');
            $table->unsignedBigInteger('received_by');
            $table->enum('status', ['pending', 'received', 'catalogued', 'distributed'])->default('pending');
            $table->string('certificate_number', 100)->nullable();
            $table->date('certificate_date')->nullable();
            $table->boolean('acknowledgement_letter_sent')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('donor_id')->references('id')->on('library_donors')->onDelete('cascade');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_donations');
    }
};
