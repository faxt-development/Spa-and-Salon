<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('staff_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->date('metric_date');
            
            // Utilization metrics
            $table->decimal('available_hours', 8, 2)->default(0);
            $table->decimal('booked_hours', 8, 2)->default(0);
            $table->decimal('utilization_rate', 5, 2)->default(0);
            
            // Revenue metrics
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('revenue_per_hour', 15, 2)->default(0);
            $table->integer('appointments_completed')->default(0);
            $table->decimal('average_ticket_value', 15, 2)->default(0);
            
            // Commission metrics
            $table->decimal('total_commission', 15, 2)->default(0);
            $table->decimal('average_commission_rate', 5, 2)->default(0);
            
            // Customer metrics
            $table->integer('new_customers')->default(0);
            $table->integer('repeat_customers')->default(0);
            $table->decimal('customer_satisfaction_score', 3, 1)->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['staff_id', 'metric_date']);
            $table->unique(['staff_id', 'metric_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_performance_metrics');
    }
};
