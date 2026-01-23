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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('contact');
            $table->string('website')->nullable()->after('email');
            $table->string('contact_person')->nullable()->after('website');
            $table->boolean('is_active')->default(true)->after('contact_person');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['phone', 'website', 'contact_person', 'is_active']);
        });
    }
};