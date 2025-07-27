<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PayrollRecord;
use App\Models\TaxRate;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{


    protected $admin;
    protected $taxRate;

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations
        Artisan::call('migrate:fresh');

        // Create an admin user
        $this->admin = User::factory()->create([
            'role' => 'admin'
        ]);

        // Create a tax rate
        $this->taxRate = TaxRate::create([
            'name' => 'Sales Tax',
            'rate' => 7.5,
            'type' => 'sales',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/reports/tax/summary');
        $response->assertStatus(401);

        $response = $this->getJson('/api/reports/tax/detailed');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_requires_admin_permission()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/reports/tax/summary');

        $response->assertStatus(403);
    }

    /** @test */
    public function it_returns_tax_summary_report()
    {
        // Create test data
        $order = Order::factory()->create([
            'status' => 'completed',
            'created_at' => Carbon::now()->subDay(),
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'tax_rate_id' => $this->taxRate->id,
            'quantity' => 2,
            'unit_price' => 100,
            'tax_amount' => 15, // 2 * 100 * 0.075 = 15
        ]);

        // Make request
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/reports/tax/summary?start_date=' . Carbon::now()->subWeek()->format('Y-m-d') . '&end_date=' . Carbon::now()->format('Y-m-d'));

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'start_date',
                    'end_date',
                    'group_by',
                    'total_taxable_amount',
                    'total_tax_amount',
                    'results' => [
                        '*' => [
                            'period',
                            'total_taxable_amount',
                            'total_tax_amount',
                            'breakdown' => [
                                '*' => [
                                    'source',
                                    'tax_rate_name',
                                    'tax_rate',
                                    'tax_type',
                                    'taxable_amount',
                                    'tax_amount',
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        // Assert data
        $responseData = $response->json('data');
        $this->assertEquals(200, $responseData['total_taxable_amount']);
        $this->assertEquals(15, $responseData['total_tax_amount']);
    }

    /** @test */
    public function it_returns_detailed_tax_report()
    {
        // Create test data
        $order = Order::factory()->create([
            'status' => 'completed',
            'created_at' => Carbon::now()->subDay(),
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'tax_rate_id' => $this->taxRate->id,
            'quantity' => 2,
            'unit_price' => 100,
            'tax_amount' => 15,
        ]);

        // Create payroll record
        $payroll = PayrollRecord::factory()->create([
            'pay_period_start' => Carbon::now()->subDay(),
            'pay_period_end' => Carbon::now(),
            'payment_status' => 'processed',
            'gross_amount' => 1000,
            'tax_amount' => 200,
        ]);

        // Make request for orders only
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/reports/tax/detailed?start_date=' . Carbon::now()->subWeek()->format('Y-m-d') . '&end_date=' . Carbon::now()->format('Y-m-d') . '&type=order');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'client_id',
                            'status',
                            'total_amount',
                            'tax_amount',
                            'created_at',
                            'updated_at',
                            'client' => [
                                'id',
                                'name',
                                'email',
                                'phone',
                            ],
                            'items' => [
                                '*' => [
                                    'id',
                                    'order_id',
                                    'itemable_type',
                                    'itemable_id',
                                    'quantity',
                                    'unit_price',
                                    'tax_amount',
                                    'tax_rate_id',
                                    'created_at',
                                    'updated_at',
                                    'tax_rate' => [
                                        'id',
                                        'name',
                                        'rate',
                                        'type',
                                        'is_active',
                                        'created_at',
                                        'updated_at',
                                    ],
                                ]
                            ]
                        ]
                    ],
                    'links' => [
                        'first',
                        'last',
                        'prev',
                        'next',
                    ],
                    'meta' => [
                        'current_page',
                        'from',
                        'last_page',
                        'links' => [
                            '*' => [
                                'url',
                                'label',
                                'active',
                            ]
                        ],
                        'path',
                        'per_page',
                        'to',
                        'total',
                    ]
                ]
            ]);

        // Make request for payroll only
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/reports/tax/detailed?start_date=' . Carbon::now()->subWeek()->format('Y-m-d') . '&end_date=' . Carbon::now()->format('Y-m-d') . '&type=payroll');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data');

        // Make request for all types
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/reports/tax/detailed?start_date=' . Carbon::now()->subWeek()->format('Y-m-d') . '&end_date=' . Carbon::now()->format('Y-m-d') . '&type=all');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.data');
    }

    /** @test */
    public function it_validates_tax_summary_parameters()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/reports/tax/summary');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date', 'end_date']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/reports/tax/summary?start_date=invalid&end_date=invalid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date', 'end_date']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/reports/tax/summary?start_date=' . Carbon::now()->format('Y-m-d') . '&end_date=' . Carbon::now()->subDay()->format('Y-m-d'));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    /** @test */
    public function it_groups_tax_summary_by_different_periods()
    {
        // Create test data for different periods
        $order1 = Order::factory()->create([
            'status' => 'completed',
            'created_at' => Carbon::create(2023, 1, 1),
        ]);

        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'tax_rate_id' => $this->taxRate->id,
            'quantity' => 1,
            'unit_price' => 100,
            'tax_amount' => 7.5,
        ]);

        $order2 = Order::factory()->create([
            'status' => 'completed',
            'created_at' => Carbon::create(2023, 2, 1),
        ]);

        OrderItem::factory()->create([
            'order_id' => $order2->id,
            'tax_rate_id' => $this->taxRate->id,
            'quantity' => 2,
            'unit_price' => 100,
            'tax_amount' => 15,
        ]);

        // Test grouping by month
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/reports/tax/summary?start_date=2023-01-01&end_date=2023-12-31&group_by=month');

        $response->assertStatus(200);
        $responseData = $response->json('data');
        $this->assertCount(2, $responseData['results']);

        // Test grouping by year
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/reports/tax/summary?start_date=2023-01-01&end_date=2023-12-31&group_by=year');

        $response->assertStatus(200);
        $responseData = $response->json('data');
        $this->assertCount(1, $responseData['results']);
    }

    /** @test */
    public function it_filters_tax_summary_by_tax_rate()
    {
        // Create test data with different tax rates
        $taxRate2 = TaxRate::create([
            'name' => 'VAT',
            'rate' => 20,
            'type' => 'vat',
            'is_active' => true,
        ]);

        $order1 = Order::factory()->create(['status' => 'completed']);
        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'tax_rate_id' => $this->taxRate->id,
            'quantity' => 1,
            'unit_price' => 100,
            'tax_amount' => 7.5,
        ]);

        $order2 = Order::factory()->create(['status' => 'completed']);
        OrderItem::factory()->create([
            'order_id' => $order2->id,
            'tax_rate_id' => $taxRate2->id,
            'quantity' => 1,
            'unit_price' => 100,
            'tax_amount' => 20,
        ]);

        // Test filtering by tax rate
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/reports/tax/summary?start_date=' . Carbon::now()->subWeek()->format('Y-m-d') . '&end_date=' . Carbon::now()->format('Y-m-d') . '&tax_rate_id=' . $this->taxRate->id);

        $response->assertStatus(200);
        $responseData = $response->json('data');
        $this->assertEquals(7.5, $responseData['total_tax_amount']);
    }
}
