<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // subscription, low_stock, invoice, maintenance, system, alert
            $table->string('title');
            $table->text('message');
            $table->text('details')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_ignored')->default(false);
            $table->timestamp('timestamp')->useCurrent();
            $table->string('priority')->default('medium'); // low, medium, high
            $table->string('action_url')->nullable();
            $table->string('related_item')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
