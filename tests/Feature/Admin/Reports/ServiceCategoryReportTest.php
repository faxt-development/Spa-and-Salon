<?php

namespace Tests\Feature\Admin\Reports;

use Tests\TestCase;
use App\Models\User;
use App\Models\Service;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServiceCategoryReportTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $category1;
    private $category2;
    private $service1;
    private $service2;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        // Create test data
        $this->category1 = ServiceCategory::factory()->create(['name' => 'Hair']);
        $this->category2 = ServiceCategory::factory()->create(['name' => 'Nails']);
        
        $this->service1 = Service::factory()->create(['name' => 'Haircut', 'price' => 50.00]);
        $this->service1->categories()->attach($this->category1->id);
        
        $this->service2 = Service::factory()->create(['name' => 'Manicure', 'price' => 30.00]);
        $this->service2->categories()->attach($this->category2->id);
        
        // Create test orders
        $this->createTestOrders();
    }
    
    private function createTestOrders()
    {
        // Create orders for category 1
        $order1 = Order::factory()->create([
            'total_amount' => 100.00,
            'created_at' => now()->subDays(5)
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'itemable_id' => $this->service1->id,
            'itemable_type' => get_class($this->service1),
            'quantity' => 2,
            'unit_price' => 50.00,
            'subtotal' => 100.00,
            'service_category_id' => $this->category1->id,
            'created_at' => now()->subDays(5)
        ]);
        
        // Create orders for category 2
        $order2 = Order::factory()->create([
            'total_amount' => 90.00,
            'created_at' => now()->subDays(3)
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order2->id,
            'itemable_id' => $this->service2->id,
            'itemable_type' => get_class($this->service2),
            'quantity' => 3,
            'unit_price' => 30.00,
            'subtotal' => 90.00,
            'service_category_id' => $this->category2->id,
            'created_at' => now()->subDays(3)
        ]);
    }

    /** @test */
    public function admin_can_view_service_categories_report_page()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.service.categories'));
            
        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.service-categories');
        $response->assertViewHas('serviceCategories');
        $response->assertViewHas('defaultStartDate');
        $response->assertViewHas('defaultEndDate');
    }
    
    /** @test */
    public function it_returns_service_category_data()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.service.categories.data', [
                'start_date' => now()->subDays(30)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d')
            ]));
            
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'category_id',
                    'category_name',
                    'revenue',
                    'service_count',
                    'average_service_price'
                ]
            ]
        ]);
        
        $data = $response->json('data');
        $this->assertCount(2, $data);
        
        // Verify data for each category
        $hairCategory = collect($data)->firstWhere('category_id', $this->category1->id);
        $this->assertEquals(100.00, $hairCategory['revenue']);
        $this->assertEquals(1, $hairCategory['service_count']);
        
        $nailsCategory = collect($data)->firstWhere('category_id', $this->category2->id);
        $this->assertEquals(90.00, $nailsCategory['revenue']);
    }
    
    /** @test */
    public function it_filters_service_category_data_by_date_range()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.service.categories.data', [
                'start_date' => now()->subDays(4)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d')
            ]));
            
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->category2->id, $data[0]['category_id']);
    }
    
    /** @test */
    public function it_returns_service_performance_data()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.service.performance.data', [
                'start_date' => now()->subDays(30)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d')
            ]));
            
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'service_id',
                    'service_name',
                    'category_id',
                    'category_name',
                    'quantity_sold',
                    'revenue',
                    'average_sale_price'
                ]
            ]
        ]);
        
        $data = $response->json('data');
        $this->assertCount(2, $data);
        
        // Verify service performance data
        $haircutData = collect($data)->firstWhere('service_id', $this->service1->id);
        $this->assertEquals(100.00, $haircutData['revenue']);
        $this->assertEquals(2, $haircutData['quantity_sold']);
        
        $manicureData = collect($data)->firstWhere('service_id', $this->service2->id);
        $this->assertEquals(90.00, $manicureData['revenue']);
        $this->assertEquals(3, $manicureData['quantity_sold']);
    }
    
    /** @test */
    public function it_validates_date_parameters()
    {
        // Missing dates
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.service.categories.data'));
            
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['start_date', 'end_date']);
        
        // Invalid date format
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.service.categories.data', [
                'start_date' => 'invalid-date',
                'end_date' => 'invalid-date'
            ]));
            
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['start_date', 'end_date']);
        
        // End date before start date
        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.service.categories.data', [
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->subDays(1)->format('Y-m-d')
            ]));
            
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['end_date']);
    }
}
