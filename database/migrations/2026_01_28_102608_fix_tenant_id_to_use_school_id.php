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
        // Update tenant_id in users to use schools.id instead of centre_number
        \DB::statement('UPDATE users u JOIN schools s ON u.tenant_id = s.centre_number SET u.tenant_id = s.id');

        // Update tenant_id in categories
        \DB::statement('UPDATE categories c JOIN schools s ON c.tenant_id = s.centre_number SET c.tenant_id = s.id');

        // Update tenant_id in suppliers
        \DB::statement('UPDATE suppliers s JOIN schools sc ON s.tenant_id = sc.centre_number SET s.tenant_id = sc.id');

        // Update tenant_id in locations
        \DB::statement('UPDATE locations l JOIN schools sc ON l.tenant_id = sc.centre_number SET l.tenant_id = sc.id');

        // Update tenant_id in items
        \DB::statement('UPDATE items i JOIN schools sc ON i.tenant_id = sc.centre_number SET i.tenant_id = sc.id');

        // Update tenant_id in stock_movements
        \DB::statement('UPDATE stock_movements sm JOIN schools sc ON sm.tenant_id = sc.centre_number SET sm.tenant_id = sc.id');

        // Update tenant_id in lab_sessions
        \DB::statement('UPDATE lab_sessions ls JOIN schools sc ON ls.tenant_id = sc.centre_number SET ls.tenant_id = sc.id');

        // Update tenant_id in transactions
        \DB::statement('UPDATE transactions t JOIN schools sc ON t.tenant_id = sc.centre_number SET t.tenant_id = sc.id');

        // Update tenant_id in activity_logs
        \DB::statement('UPDATE activity_logs al JOIN schools sc ON al.tenant_id = sc.centre_number SET al.tenant_id = sc.id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('use_school_id', function (Blueprint $table) {
            //
        });
    }
};
