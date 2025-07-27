<?php

namespace Tests\Feature;

use App\Models\Staff;
use App\Models\Transaction;
use App\Models\TransactionLineItem;
use App\Models\TipDistribution;
use App\Services\TipDistributionService;
use Tests\TestCase;

class TipDistributionTest extends TestCase
{


    private $tipDistributionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tipDistributionService = app(TipDistributionService::class);
    }

    /** @test */
    public function it_can_distribute_tips_to_individual_staff_member()
    {
        // Create a staff member
        $staff = Staff::factory()->create();

        // Create a transaction with a tip
        $transaction = Transaction::factory()->create([
            'tip_amount' => 100.00,
            'staff_id' => $staff->id,
        ]);

        // Distribute tips to the individual staff member
        $distributions = $this->tipDistributionService->distributeTips(
            $transaction,
            'individual',
            []
        );

        // Refresh the transaction to get the latest state
        $transaction->refresh();

        // Assertions
        $this->assertCount(1, $distributions);
        $this->assertEquals(100.00, $distributions->first()->amount);
        $this->assertEquals($staff->id, $distributions->first()->staff_id);
        $this->assertTrue($transaction->tips_distributed, 'The tips_distributed flag should be true after distribution');
    }

    /** @test */
    public function it_can_distribute_tips_among_multiple_staff_members()
    {
        // Create staff members
        $staff1 = Staff::factory()->create();
        $staff2 = Staff::factory()->create();

        // Create a transaction with a tip
        $transaction = Transaction::factory()->create([
            'tip_amount' => 100.00,
            'staff_id' => $staff1->id, // Primary staff member
        ]);

        // Add line items with different staff members
        TransactionLineItem::factory()->create([
            'transaction_id' => $transaction->id,
            'staff_id' => $staff1->id,
            'item_type' => 'service',
            'amount' => 50.00,
        ]);

        TransactionLineItem::factory()->create([
            'transaction_id' => $transaction->id,
            'staff_id' => $staff2->id,
            'item_type' => 'service',
            'amount' => 50.00,
        ]);

        // Distribute tips using the pooled method
        $distributions = $this->tipDistributionService->distributeTips(
            $transaction,
            'pooled',
            []
        );

        // Assertions
        $this->assertCount(2, $distributions);
        $this->assertEquals(100.00, $distributions->sum('amount')); // Total should be 100.00, split between two staff
        $this->assertTrue($distributions->where('staff_id', $staff1->id)->isNotEmpty());
        $this->assertTrue($distributions->where('staff_id', $staff2->id)->isNotEmpty());
    }

    /** @test */
    public function it_can_distribute_tips_using_custom_split()
    {
        // Create staff members
        $staff1 = Staff::factory()->create();
        $staff2 = Staff::factory()->create();

        // Create a transaction with a tip
        $transaction = Transaction::factory()->create([
            'tip_amount' => 100.00,
        ]);

        // Distribute tips using custom split
        $distributions = $this->tipDistributionService->distributeTips(
            $transaction,
            'split',
            [
                $staff1->id => 70, // 70%
                $staff2->id => 30, // 30%
            ]
        );

        // Assertions
        $this->assertCount(2, $distributions);

        $staff1Distribution = $distributions->firstWhere('staff_id', $staff1->id);
        $staff2Distribution = $distributions->firstWhere('staff_id', $staff2->id);

        $this->assertEquals(70.00, $staff1Distribution->amount);
        $this->assertEquals(30.00, $staff2Distribution->amount);
    }

    /** @test */
    public function it_prevents_double_distribution()
    {
        // Create a staff member and transaction
        $staff = Staff::factory()->create();
        $transaction = Transaction::factory()->create([
            'tip_amount' => 100.00,
            'staff_id' => $staff->id,
        ]);

        // First distribution should succeed
        $this->tipDistributionService->distributeTips($transaction, 'individual', []);

        // Second distribution should fail
        $this->expectException(\RuntimeException::class);
        $this->tipDistributionService->distributeTips($transaction, 'individual', []);
    }

    /** @test */
    public function it_can_get_tip_distribution_summary()
    {
        // Create staff members
        $staff1 = Staff::factory()->create();
        $staff2 = Staff::factory()->create();

        // Create transactions with tips
        $transaction1 = Transaction::factory()->create(['tip_amount' => 50.00]);
        $transaction2 = Transaction::factory()->create(['tip_amount' => 100.00]);

        // Create tip distributions
        TipDistribution::create([
            'transaction_id' => $transaction1->id,
            'staff_id' => $staff1->id,
            'amount' => 50.00,
            'percentage' => 100.00,
            'is_processed' => true,
        ]);

        TipDistribution::create([
            'transaction_id' => $transaction2->id,
            'staff_id' => $staff2->id,
            'amount' => 100.00,
            'percentage' => 100.00,
            'is_processed' => true,
        ]);

        // Get summary
        $summary = $this->tipDistributionService->getTipDistributionSummary();

        // Assertions
        $this->assertCount(2, $summary);
        $this->assertEquals(50.00, $summary->firstWhere('staff_id', $staff1->id)->total_tips);
        $this->assertEquals(100.00, $summary->firstWhere('staff_id', $staff2->id)->total_tips);
    }
}
