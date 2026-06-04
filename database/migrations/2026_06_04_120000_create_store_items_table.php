<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->nullable();
            $table->foreign('tenant_id')->references('id')->on('schools')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('category')->nullable();
            $table->string('avatar')->nullable();
            $table->json('images')->nullable();
            $table->string('color')->nullable();
            $table->string('brand')->nullable();
            $table->integer('in_stock')->nullable();
            $table->integer('min_quantity')->nullable();
            $table->string('condition')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('discount', 12, 2)->nullable();
            $table->text('desc')->nullable();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'category']);
            $table->index(['tenant_id', 'in_stock']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_items');
    }
};
