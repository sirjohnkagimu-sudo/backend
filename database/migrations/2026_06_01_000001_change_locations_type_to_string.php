<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('type')->default('shelf')->change();
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->enum('type', ['shelf', 'drawer', 'bench', 'cabinet'])->default('shelf')->change();
        });
    }
};
