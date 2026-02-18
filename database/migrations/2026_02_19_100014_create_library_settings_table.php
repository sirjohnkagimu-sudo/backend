<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->unique();
            $table->string('academic_year', 20)->nullable();
            $table->enum('current_term', ['Term 1', 'Term 2', 'Term 3'])->default('Term 1');
            $table->json('term_dates')->nullable();
            $table->json('borrowing_rules')->nullable();
            $table->json('clearance_settings')->nullable();
            $table->json('contact_info')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_settings');
    }
};
