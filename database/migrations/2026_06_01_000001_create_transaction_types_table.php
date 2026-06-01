<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_types', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->string('name');
            $table->string('color')->default('bg-gray-100 text-gray-800');
            $table->string('icon')->default('📝');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_types');
    }
};
