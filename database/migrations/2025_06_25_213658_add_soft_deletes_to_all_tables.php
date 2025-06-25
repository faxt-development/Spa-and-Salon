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
        // List of tables to add soft delete functionality
        $tables = [
            'appointments',
            'clients',
            'drip_campaign_recipients',
            'drip_campaigns',
            'email_campaigns',
            'email_recipients',
            'employees',
            'gift_cards',
            'inventories',
            'inventory_transactions',
            'order_items',
            'orders',
            'payments',
            'payroll_records',
            'permissions',
            'product_categories',
            'products',
            'promotion_usages',
            'promotions',
            'revenue_events',
            'roles',
            'rooms',
            'service_categories',
            'services',
            'settings',
            'staff',
            'subscriptions',
            'suppliers',
            'tax_rates',
            'time_clock_entries',
            'transaction_line_items',
            'transactions',
            'users',
            'walk_ins',
        ];

        // Add deleted_at column to each table
        foreach ($tables as $tableName) {
            // Check if the column exists before trying to add it
            if (!Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // List of tables to remove soft delete functionality
        $tables = [
            'appointments',
            'clients',
            'drip_campaign_recipients',
            'drip_campaigns',
            'email_campaigns',
            'email_recipients',
            'employees',
            'gift_cards',
            'inventories',
            'inventory_transactions',
            'order_items',
            'orders',
            'payments',
            'payroll_records',
            'permissions',
            'product_categories',
            'products',
            'promotion_usages',
            'promotions',
            'revenue_events',
            'roles',
            'rooms',
            'service_categories',
            'services',
            'settings',
            'staff',
            'subscriptions',
            'suppliers',
            'tax_rates',
            'time_clock_entries',
            'transaction_line_items',
            'transactions',
            'users',
            'walk_ins',
        ];

        // Remove deleted_at column from each table
        foreach ($tables as $tableName) {
            // Check if the column exists before trying to remove it
            if (Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
