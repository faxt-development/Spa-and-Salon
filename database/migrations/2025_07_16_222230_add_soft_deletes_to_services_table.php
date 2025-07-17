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
        // Add soft deletes to services table
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'deleted_at')) {
                $table->softDeletes();
            }
            
            // Add template field
            if (!Schema::hasColumn('services', 'template')) {
                $table->boolean('template')->default(false)->after('deleted_at');
            }
        });

        // Add soft deletes to company_service pivot table
        if (Schema::hasTable('company_service')) {
            Schema::table('company_service', function (Blueprint $table) {
                if (!Schema::hasColumn('company_service', 'deleted_at')) {
                    $table->timestamp('deleted_at')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove soft deletes from services table
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            
            // Remove template field
            if (Schema::hasColumn('services', 'template')) {
                $table->dropColumn('template');
            }
        });

        // Remove soft deletes from company_service pivot table
        if (Schema::hasTable('company_service')) {
            Schema::table('company_service', function (Blueprint $table) {
                if (Schema::hasColumn('company_service', 'deleted_at')) {
                    $table->dropColumn('deleted_at');
                }
            });
        }
    }
};
