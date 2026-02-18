<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('title_id');
            $table->string('barcode', 100)->nullable();
            $table->integer('copy_number')->default(1);
            $table->enum('status', ['available', 'borrowed', 'lost', 'damaged', 'under-maintenance'])->default('available');
            $table->enum('condition', ['new', 'good', 'torn', 'missing-pages', 'lost'])->default('good');
            $table->string('location', 100)->nullable();
            $table->string('donated_by', 255)->nullable();
            $table->date('donation_date')->nullable();
            $table->date('acquired_date')->nullable();
            $table->date('last_inspection_date')->nullable();
            $table->json('chain_of_custody')->nullable();
            $table->unsignedBigInteger('current_holder_id')->nullable();
            $table->string('current_holder_type', 50)->nullable(); // student, staff, teacher
            $table->date('last_checkout_date')->nullable();
            $table->date('expected_return_date')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->foreign('title_id')->references('id')->on('book_titles')->onDelete('cascade');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'title_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_copies');
    }
};
