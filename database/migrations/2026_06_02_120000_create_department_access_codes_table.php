<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_access_codes', function (Blueprint $table) {
            $table->id();
            $table->uuid('school_id');
            $table->string('access_code');
            $table->string('user_name');
            $table->string('email')->nullable();
            $table->string('role');
            $table->json('permissions')->nullable();
            $table->string('department');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'access_code']);
            $table->index('department');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_access_codes');
    }
};
