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
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->string('code', 50)->unique();
            $table->decimal('amount', 10, 2);
            $table->decimal('balance', 10, 2);
            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();
            $table->string('sender_name')->nullable();
            $table->text('message')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_redeemed')->default(false);
            $table->dateTime('redeemed_at')->nullable();
            $table->foreignId('redeemed_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->dropColumn([
                'code',
                'amount',
                'balance',
                'recipient_name',
                'recipient_email',
                'sender_name',
                'message',
                'expires_at',
                'is_active',
                'is_redeemed',
                'redeemed_at',
                'redeemed_by',
                'notes'
            ]);
        });
    }
};
