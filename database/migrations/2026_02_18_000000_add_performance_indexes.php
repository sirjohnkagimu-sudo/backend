<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add performance indexes for frequently queried columns.
     */
    public function up(): void
    {
        // Items table indexes
        Schema::table('items', function (Blueprint $table) {
            $table->index(['tenant_id', 'quantity'], 'idx_items_tenant_quantity');
            $table->index(['tenant_id', 'min_quantity'], 'idx_items_tenant_min_qty');
        });

        // Lab access codes indexes
        Schema::table('lab_access_codes', function (Blueprint $table) {
            $table->index(['school_id', 'is_active'], 'idx_lab_access_school_active');
            $table->index(['school_id', 'last_used_at'], 'idx_lab_access_school_lastused');
        });

        // Notifications indexes
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['is_ignored', 'is_read', 'timestamp'], 'idx_notifs_ignored_read_time');
            $table->index(['user_id', 'is_read'], 'idx_notifs_user_read');
        });

        // Stock movements indexes
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['tenant_id', 'created_at'], 'idx_stock_tenant_created');
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index(['tenant_id', 'department'], 'idx_users_tenant_dept');
            $table->index(['tenant_id', 'role_id'], 'idx_users_tenant_role');
        });

        // Orders indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_orders_user_created');
            $table->index(['user_id', 'payment_status'], 'idx_orders_user_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes (in reverse order)
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('idx_items_tenant_quantity');
            $table->dropIndex('idx_items_tenant_min_qty');
        });

        Schema::table('lab_access_codes', function (Blueprint $table) {
            $table->dropIndex('idx_lab_access_school_active');
            $table->dropIndex('idx_lab_access_school_lastused');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifs_ignored_read_time');
            $table->dropIndex('idx_notifs_user_read');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('idx_stock_tenant_created');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_tenant_dept');
            $table->dropIndex('idx_users_tenant_role');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_user_created');
            $table->dropIndex('idx_orders_user_status');
        });
    }
};
