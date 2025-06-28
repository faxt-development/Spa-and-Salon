<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Loyalty Programs
        Schema::create('loyalty_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('points_per_currency', 10, 2)->default(1);
            $table->decimal('currency_per_point', 10, 2)->default(1);
            $table->integer('signup_points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Loyalty Accounts
        Schema::create('loyalty_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loyalty_program_id')->constrained()->cascadeOnDelete();
            $table->integer('points_balance')->default(0);
            $table->integer('points_earned_lifetime')->default(0);
            $table->integer('points_redeemed_lifetime')->default(0);
            $table->string('tier')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            
            $table->unique(['client_id', 'loyalty_program_id']);
        });

        // Loyalty Transactions
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_account_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // earn, redeem, expire, adjust
            $table->integer('points');
            $table->decimal('points_value', 10, 2)->nullable();
            $table->string('reference_type')->nullable(); // order, adjustment, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            
            // For point expiration
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users');
            
            $table->timestamps();
            
            $table->index(['reference_type', 'reference_id']);
        });

        // Loyalty Tiers
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_program_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('points_required')->default(0);
            $table->decimal('multiplier', 5, 2)->default(1.0);
            $table->json('benefits')->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();
        });

        // Add loyalty fields to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('loyalty_points_earned', 10, 2)->default(0)->after('total_amount');
            $table->decimal('loyalty_points_redeemed', 10, 2)->default(0)->after('loyalty_points_earned');
            $table->decimal('loyalty_discount_amount', 10, 2)->default(0)->after('loyalty_points_redeemed');
        });

        // Add original price to order items
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('original_price', 10, 2)->after('unit_price');
            $table->string('discount_type')->nullable()->after('discount');
            $table->foreignId('promotion_id')->nullable()->after('discount_type')
                ->constrained('promotions')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['original_price', 'discount_type', 'promotion_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'loyalty_points_earned',
                'loyalty_points_redeemed',
                'loyalty_discount_amount'
            ]);
        });

        Schema::dropIfExists('loyalty_tiers');
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('loyalty_accounts');
        Schema::dropIfExists('loyalty_programs');
    }
};
