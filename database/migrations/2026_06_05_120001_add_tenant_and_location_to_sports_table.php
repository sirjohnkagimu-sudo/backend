<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            if (!Schema::hasColumn('sports', 'tenant_id')) {
                $table->uuid('tenant_id')->nullable()->after('id');
                $table->index('tenant_id');
            }
            if (!Schema::hasColumn('sports', 'location_id')) {
                $table->unsignedInteger('location_id')->nullable()->after('tenant_id');
                $table->index('location_id');
            }
            if (!Schema::hasColumn('sports', 'supplier_id')) {
                $table->unsignedInteger('supplier_id')->nullable()->after('location_id');
                $table->index('supplier_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropIndex(['location_id']);
            $table->dropIndex(['supplier_id']);
            $table->dropColumn(['tenant_id', 'location_id', 'supplier_id', 'min_quantity', 'code']);
        });
    }
};
