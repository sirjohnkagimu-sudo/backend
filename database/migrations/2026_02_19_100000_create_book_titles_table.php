<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_titles', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->string('isbn', 50)->nullable();
            $table->string('title', 500);
            $table->string('author', 255)->nullable();
            $table->string('publisher', 255)->nullable();
            $table->year('year')->nullable();
            $table->string('subject', 100)->nullable();
            $table->string('class', 20)->nullable(); // S1, S2, P6, etc.
            $table->enum('category', ['textbook', 'reference', 'fiction', 'magazine', 'journal'])->default('textbook');
            $table->decimal('replacement_cost', 12, 2)->default(0);
            $table->integer('total_copies')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->index(['tenant_id', 'class']);
            $table->index(['tenant_id', 'subject']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_titles');
    }
};
