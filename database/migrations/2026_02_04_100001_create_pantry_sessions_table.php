<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pantry_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->index();
            $table->unsignedBigInteger('pantry_id')->nullable();
            $table->string('title');
            $table->enum('type', ['breakfast', 'lunch', 'dinner', 'snack', 'special']);
            $table->text('description')->nullable();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('expected_pax')->default(0);
            $table->integer('actual_pax')->nullable();
            $table->string('instructor')->nullable();
            $table->json('required_items')->nullable();
            $table->enum('status', ['planned', 'ongoing', 'completed', 'cancelled'])->default('planned');
            $table->text('notes')->nullable();
            $table->text('menu')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'date']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pantry_sessions');
    }
};
