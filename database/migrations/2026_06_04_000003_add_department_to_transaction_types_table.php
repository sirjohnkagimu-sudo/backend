<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_types', function (Blueprint $table) {
            $table->string('department')->nullable()->after('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_types', function (Blueprint $table) {
            $table->dropColumn('department');
        });
    }
};
