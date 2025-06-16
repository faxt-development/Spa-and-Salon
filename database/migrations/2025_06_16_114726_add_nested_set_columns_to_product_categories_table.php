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
        Schema::table('product_categories', function (Blueprint $table) {
            // Make slug nullable if it's not already
            $table->string('slug')->nullable()->change();
            
            // Drop existing foreign key constraint if it exists
            $table->dropForeign(['parent_id']);
            
            // Change parent_id to unsignedBigInteger to match the id type
            $table->unsignedBigInteger('parent_id')->nullable()->change();
            
            // Add nested set columns
            $table->unsignedInteger('_lft')->default(0);
            $table->unsignedInteger('_rgt')->default(0);
            
            // Re-add the foreign key constraint
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('product_categories')
                  ->onDelete('set null');
            
            // Add indexes
            $table->index(['_lft', '_rgt', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            // Drop the index first
            $table->dropIndex(['_lft', '_rgt', 'parent_id']);
            
            // Drop the nested set columns
            $table->dropColumn(['_lft', '_rgt']);
            
            // Change parent_id back to its original state if needed
            // Since we can't easily revert the parent_id type change,
            // you might need to handle this manually if you need to rollback
            
            // Revert slug to not nullable if needed
            $table->string('slug')->nullable(false)->change();
        });
    }
};
