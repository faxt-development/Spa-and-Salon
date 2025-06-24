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
        Schema::table('payments', function (Blueprint $table) {
            // Add the appointment_id column as nullable first
            $table->unsignedBigInteger('appointment_id')->nullable()->after('id');
            
            // Add foreign key constraint
            $table->foreign('appointment_id')
                  ->references('id')
                  ->on('appointments')
                  ->onDelete('set null');
                  
            // Add index for better query performance
            $table->index('appointment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['appointment_id']);
            
            // Then drop the column
            $table->dropColumn('appointment_id');
        });
    }
};
