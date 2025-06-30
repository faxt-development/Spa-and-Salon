<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Service;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ServiceCategory;
use App\Services\FinancialReportingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinancialReportingServiceTest extends TestCase
{
    use RefreshDatabase;

    private $reportingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportingService = new FinancialReportingService();
    }

    /** @test */
    public function it_can_get_revenue_by_service_category()
    {
        // Create test data
        $category1 = ServiceCategory::factory()->create(['name' => 'Hair']);
        $category2 = ServiceCategory::factory()->create(['name' => 'Nails']);
        
        $service1 = Service::factory()->create(['price' => 50.00]);
        $service1->categories()->attach($category1->id);
        
        $service2 = Service::factory()->create(['price' => 30.00]);
        $service2->categories()->attach($category2->id);
        
        // Create orders with items
        $order1 = Order::factory()->create(['total_amount' => 100.00]);
        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'itemable_id' => $service1->id,
            'itemable_type' => get_class($service1),
            'quantity' => 2,
            'unit_price' => 50.00,
            'subtotal' => 100.00,
            'service_category_id' => $category1->id
        ]);

        $order2 = Order::factory()->create(['total_amount' => 90.00]);
        OrderItem::factory()->create([
            'order_id' => $order2->id,
            'itemable_id' => $service2->id,
            'itemable_type' => get_class($service2),
            'quantity' => 3,
            'unit_price' => 30.00,
            'subtotal' => 90.00,
            'service_category_id' => $category2->id
        ]);

        // Test without filters
        $result = $this->reportingService->getRevenueByServiceCategory(
            now()->subDays(30),
            now()
        );

        $this->assertCount(2, $result);
        
        // Verify category data
        $hairCategory = collect($result)->firstWhere('category_id', $category1->id);
        $this->assertEquals(100.00, $hairCategory['revenue']);
        $this->assertEquals(1, $hairCategory['service_count']);
        $this->assertEquals(50.00, $hairCategory['average_service_price']);

        // Test with category filter
        $filteredResult = $this->reportingService->getRevenueByServiceCategory(
            now()->subDays(30),
            now(),
            ['category_id' => $category1->id]
        );

        $this->assertCount(1, $filteredResult);
        $this->assertEquals($category1->id, $filteredResult[0]['category_id']);
    }

    /** @test */
    public function it_can_get_service_performance_by_category()
    {
        $category = ServiceCategory::factory()->create(['name' => 'Hair']);
        $service1 = Service::factory()->create(['name' => 'Haircut', 'price' => 50.00]);
        $service1->categories()->attach($category->id);
        
        $service2 = Service::factory()->create(['name' => 'Coloring', 'price' => 80.00]);
        $service2->categories()->attach($category->id);

        // Create orders with items
        $order1 = Order::factory()->create(['total_amount' => 180.00]);
        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'itemable_id' => $service1->id,
            'itemable_type' => get_class($service1),
            'quantity' => 2,
            'unit_price' => 50.00,
            'subtotal' => 100.00,
            'service_category_id' => $category->id
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'itemable_id' => $service2->id,
            'itemable_type' => get_class($service2),
            'quantity' => 1,
            'unit_price' => 80.00,
            'subtotal' => 80.00,
            'service_category_id' => $category->id
        ]);

        $result = $this->reportingService->getServicePerformanceByCategory(
            now()->subDays(30),
            now()
        );

        $this->assertCount(2, $result);
        
        $haircutData = collect($result)->firstWhere('service_id', $service1->id);
        $this->assertEquals(100.00, $haircutData['revenue']);
        $this->assertEquals(2, $haircutData['quantity_sold']);
        $this->assertEquals(50.00, $haircutData['average_sale_price']);
    }
}
