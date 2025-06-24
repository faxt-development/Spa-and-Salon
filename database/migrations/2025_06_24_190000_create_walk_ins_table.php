<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('walk_ins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->integer('party_size')->default(1);
            $table->text('notes')->nullable();
            $table->enum('status', ['waiting', 'in_service', 'completed', 'cancelled'])->default('waiting');
            $table->integer('estimated_wait_time')->nullable()->comment('Estimated wait time in minutes');
            $table->timestamp('check_in_time')->useCurrent();
            $table->timestamp('service_start_time')->nullable();
            $table->timestamp('service_end_time')->nullable();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('walk_ins');
    }
};
