<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->string('category')->change();
            $table->string('condition')->change();
        });
    }

    public function down(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->enum('category', ['balls', 'jerseys', 'board_games', 'indoor_games'])->change();
            $table->enum('condition', ['new', 'old'])->change();
        });
    }
};
