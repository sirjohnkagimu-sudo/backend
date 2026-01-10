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
        Schema::table('schools', function (Blueprint $table) {
            $table->string('name');
            $table->string('centre_number')->unique();
            $table->string('district')->nullable();
            $table->string('admin_name')->nullable();
            $table->string('admin_email')->nullable();
            $table->string('admin_phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['name', 'centre_number', 'district', 'admin_name', 'admin_email', 'admin_phone']);
        });
    }
};
