<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSickbayMedicinesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sickbay_medicines', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->string('name', 200);
            $table->string('category', 100)->nullable();
            $table->string('dosage', 100)->nullable();
            $table->string('unit', 50)->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('min_quantity')->default(10);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('supplier', 200)->nullable();
            $table->text('storage_location')->nullable();
            $table->text('instructions')->nullable();
            $table->text('side_effects')->nullable();
            $table->boolean('requires_prescription')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->index(['tenant_id', 'name']);
            $table->index(['tenant_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sickbay_medicines');
    }
}
