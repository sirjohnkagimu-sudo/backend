<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'institution_name',
                'centre_number',
                'district',
                'adminName',
                'adminEmail',
                'adminPhone',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('institution_name')->nullable();
            $table->string('centre_number')->nullable();
            $table->string('district')->nullable();

            $table->string('adminName')->nullable();
            $table->string('adminEmail')->nullable();
            $table->string('adminPhone')->nullable();
            $table->boolean('is_school_admin')->default(false);
        });
    }
};
