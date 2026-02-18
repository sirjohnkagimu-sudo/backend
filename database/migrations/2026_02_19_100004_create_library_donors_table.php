<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_donors', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->string('name', 255);
            $table->enum('type', ['individual', 'organization', 'government', 'ngo', 'alumni'])->default('individual');
            $table->string('contact_person', 255)->nullable();
            $table->string('email', 200)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->integer('donation_count')->default(0);
            $table->integer('total_books_donated')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->index(['tenant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_donors');
    }
};
