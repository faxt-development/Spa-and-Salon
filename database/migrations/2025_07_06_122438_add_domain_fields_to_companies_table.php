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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('domain')->nullable()->unique()->after('website');
            $table->boolean('is_primary_domain')->default(false)->after('domain');
            $table->json('homepage_content')->nullable()->after('is_primary_domain');
            $table->json('theme_settings')->nullable()->after('homepage_content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('domain')->nullable()->unique()->after('website');
            $table->boolean('is_primary_domain')->default(false)->after('domain');
            $table->json('homepage_content')->nullable()->after('is_primary_domain');
            $table->json('theme_settings')->nullable()->after('homepage_content');
        });
    }
};
