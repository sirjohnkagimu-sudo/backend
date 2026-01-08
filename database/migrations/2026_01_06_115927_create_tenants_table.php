<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenants')) {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable()->unique(); // or whatever you want to identify tenants
            $table->json('data')->nullable(); // optional JSON data for tenancy
            $table->timestamps();
        });
         }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
