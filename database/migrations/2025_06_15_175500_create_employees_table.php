<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('position');
            $table->string('employment_type')->default('full-time'); // full-time, part-time, contract
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('payment_frequency')->default('bi-weekly'); // weekly, bi-weekly, monthly
            $table->string('tax_id')->nullable();
            $table->text('address')->nullable();
            $table->text('emergency_contact')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
