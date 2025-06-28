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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // User who performed the action
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Action performed (e.g., 'created', 'updated', 'deleted', 'login', 'checkout')
            $table->string('action');
            
            // Human-readable description of the action
            $table->text('description');
            
            // IP address and user agent for the request
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Polymorphic relation to the auditable model
            $table->nullableMorphs('auditable');
            
            // Optional related model (e.g., for 'assigned_to' relationships)
            $table->nullableMorphs('related');
            
            // JSON metadata with additional context
            $table->json('metadata')->nullable();
            
            // When the action occurred
            $table->timestamp('occurred_at');
            
            // Indexes for performance
            $table->index(['action', 'occurred_at']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
