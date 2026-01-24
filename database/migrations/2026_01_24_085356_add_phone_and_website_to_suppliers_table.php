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
        if (Schema::hasTable('suppliers')) {
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('suppliers', 'website')) {
                $table->string('website')->nullable();
            }
            if (!Schema::hasColumn('suppliers', 'contact_person')) {
                $table->string('contact_person')->nullable();
            }
            if (!Schema::hasColumn('suppliers', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('suppliers', 'phone')) {
                $columnsToDrop[] = 'phone';
            }
            if (Schema::hasColumn('suppliers', 'website')) {
                $columnsToDrop[] = 'website';
            }
            if (Schema::hasColumn('suppliers', 'contact_person')) {
                $columnsToDrop[] = 'contact_person';
            }
            if (Schema::hasColumn('suppliers', 'is_active')) {
                $columnsToDrop[] = 'is_active';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
