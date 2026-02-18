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
            try {
                $table->index(['tenant_id', 'quantity'], 'idx_items_tenant_quantity');
            } catch (\Exception $e) {
                // Index may already exist, ignore
            }
            try {
                $table->index(['tenant_id', 'min_quantity'], 'idx_items_tenant_min_qty');
            } catch (\Exception $e) {
                // Index may already exist, ignore
            }
        });

        // Lab access codes indexes
        Schema::table('lab_access_codes', function (Blueprint $table) {
            try {
                $table->index(['school_id', 'is_active'], 'idx_lab_access_school_active');
            } catch (\Exception $e) {}
            try {
                $table->index(['school_id', 'last_used_at'], 'idx_lab_access_school_lastused');
            } catch (\Exception $e) {}
        });

        // Notifications indexes
        Schema::table('notifications', function (Blueprint $table) {
            try {
                $table->index(['is_ignored', 'is_read', 'timestamp'], 'idx_notifs_ignored_read_time');
            } catch (\Exception $e) {}
            try {
                $table->index(['user_id', 'is_read'], 'idx_notifs_user_read');
            } catch (\Exception $e) {}
        });

        // Stock movements indexes
        Schema::table('stock_movements', function (Blueprint $table) {
            try {
                $table->index(['tenant_id', 'created_at'], 'idx_stock_tenant_created');
            } catch (\Exception $e) {}
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->index(['tenant_id', 'department'], 'idx_users_tenant_dept');
            } catch (\Exception $e) {}
            try {
                $table->index(['tenant_id', 'role_id'], 'idx_users_tenant_role');
            } catch (\Exception $e) {}
        });

        // Orders indexes
        Schema::table('orders', function (Blueprint $table) {
            try {
                $table->index(['user_id', 'created_at'], 'idx_orders_user_created');
            } catch (\Exception $e) {}
            try {
                $table->index(['user_id', 'payment_status'], 'idx_orders_user_status');
            } catch (\Exception $e) {}
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
