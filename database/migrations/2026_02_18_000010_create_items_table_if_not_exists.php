<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Create items table if it doesn't exist (for servers missing base table).
     */
    public function up(): void
    {
        // Check if items table exists
        $tableExists = Schema::hasTable('items');

        if (!$tableExists) {
            Schema::create('items', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('sku')->unique()->nullable();
                $table->string('category')->nullable();
                $table->string('subcategory')->nullable();
                $table->integer('quantity')->default(0);
                $table->integer('min_quantity')->default(0);
                $table->integer('max_quantity')->nullable();
                $table->decimal('unit_cost', 10, 2)->nullable();
                $table->decimal('selling_price', 10, 2)->nullable();
                $table->decimal('total_value', 15, 2)->nullable();
                $table->string('location')->nullable();
                $table->string('supplier')->nullable();
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('tenant_id')->nullable();
                $table->string('department')->nullable();
                $table->timestamps();
            });
        }

        // Add performance indexes to items table (idempotent)
        try {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_items_tenant_quantity ON items (tenant_id, quantity)');
        } catch (\Exception $e) {
            // Index may already exist
        }
        try {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_items_tenant_min_qty ON items (tenant_id, min_quantity)');
        } catch (\Exception $e) {
            // Index may already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration only creates/fixes - don't drop
    }
};
