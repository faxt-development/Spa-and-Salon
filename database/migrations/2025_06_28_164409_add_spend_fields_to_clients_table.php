<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('source')->nullable()->after('last_visit')->comment('How the client was acquired');
            $table->decimal('total_spent', 10, 2)->default(0)->after('source');
            $table->unsignedInteger('visit_count')->default(0)->after('total_spent');
            $table->timestamp('first_visit_at')->nullable()->after('visit_count');

            // Indexes for performance
            $table->index('total_spent');
            $table->index('visit_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['source', 'total_spent', 'visit_count', 'first_visit_at']);
        });
    }
};
