<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add loyalty fields to orders
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'loyalty_points_earned')) {
                $table->decimal('loyalty_points_earned', 10, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('orders', 'loyalty_points_redeemed')) {
                $table->decimal('loyalty_points_redeemed', 10, 2)->default(0)->after('loyalty_points_earned');
            }
            if (!Schema::hasColumn('orders', 'loyalty_discount_amount')) {
                $table->decimal('loyalty_discount_amount', 10, 2)->default(0)->after('loyalty_points_redeemed');
            }
        });

        // Add discount fields to order_items
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'original_price')) {
                $table->decimal('original_price', 10, 2)->after('unit_price');
            }
            if (!Schema::hasColumn('order_items', 'discount_type')) {
                $table->string('discount_type')->nullable()->after('discount');
            }
            if (!Schema::hasColumn('order_items', 'promotion_id')) {
                $table->foreignId('promotion_id')->nullable()->after('discount_type')
                    ->constrained('promotions')->nullOnDelete();
            }
        });

        // Update existing data
        DB::statement('UPDATE order_items SET original_price = unit_price WHERE original_price IS NULL');
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'loyalty_points_earned',
                'loyalty_points_redeemed',
                'loyalty_discount_amount'
            ]);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['original_price', 'discount_type', 'promotion_id']);
        });
    }
};
