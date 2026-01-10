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
        if (!Schema::hasTable('lab_access_codes')) {
                Schema::create('lab_access_codes', function (Blueprint $table) {
                    $table->id();
                    $table->uuid('school_id');
                    $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
                    $table->string('access_code')->unique();
                    $table->string('user_name');
                    $table->string('email')->nullable();
                    $table->string('role')->nullable();
                    $table->json('permissions');
                    $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
                    $table->timestamp('expires_at')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->timestamps();
                });
            }
 }   

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_access_codes');
    }
};
