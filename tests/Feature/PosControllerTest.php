<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Service;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PosControllerTest extends TestCase
{
    use RefreshDatabase;

    private $staff;
    private $service;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create staff user
        $this->staff = User::factory()->create(['role' => 'staff']);
        
        // Create service category
        $this->category = ServiceCategory::factory()->create(['name' => 'Hair']);
        
        // Create service with category
        $this->service = Service::factory()->create([
            'name' => 'Haircut',
            'price' => 50.00,
            'duration' => 30,
            'is_active' => true
        ]);
        $this->service->categories()->attach($this->category->id);
    }

    /** @test */
    public function it_assigns_service_category_to_order_item()
    {
        $cart = [
            'items' => [
                [
                    'id' => $this->service->id,
                    'type' => 'service',
                    'name' => $this->service->name,
                    'price' => $this->service->price,
                    'quantity' => 1,
                    'staff_id' => $this->staff->id,
                    'duration' => $this->service->duration
                ]
            ],
            'subtotal' => 50.00,
            'tax' => 0.00,
            'total' => 50.00,
            'payment_method' => 'cash',
            'customer_id' => null,
            'notes' => ''
        ];

        $response = $this->actingAs($this->staff)
            ->post(route('pos.process-payment'), $cart);

        $response->assertStatus(200);
        
        // Verify order was created
        $this->assertDatabaseHas('orders', [
            'total_amount' => 50.00,
            'status' => 'completed'
        ]);
        
        // Verify order item has the correct category
        $order = Order::first();
        $orderItem = $order->items()->first();
        
        $this->assertNotNull($orderItem->service_category_id);
        $this->assertEquals($this->category->id, $orderItem->service_category_id);
        
        // Verify the relationship works
        $this->assertEquals($this->category->id, $orderItem->serviceCategory->id);
        $this->assertEquals('Hair', $orderItem->serviceCategory->name);
    }
    
    /** @test */
    public function it_handles_service_without_category()
    {
        // Create a service without categories
        $serviceWithoutCategory = Service::factory()->create([
            'name' => 'Special Treatment',
            'price' => 100.00,
            'duration' => 60,
            'is_active' => true
        ]);
        
        $cart = [
            'items' => [
                [
                    'id' => $serviceWithoutCategory->id,
                    'type' => 'service',
                    'name' => $serviceWithoutCategory->name,
                    'price' => $serviceWithoutCategory->price,
                    'quantity' => 1,
                    'staff_id' => $this->staff->id,
                    'duration' => $serviceWithoutCategory->duration
                ]
            ],
            'subtotal' => 100.00,
            'tax' => 0.00,
            'total' => 100.00,
            'payment_method' => 'cash',
            'customer_id' => null,
            'notes' => ''
        ];

        $response = $this->actingAs($this->staff)
            ->post(route('pos.process-payment'), $cart);

        $response->assertStatus(200);
        
        // Verify order was created
        $order = Order::first();
        $this->assertNotNull($order);
        
        // Verify order item has no category
        $orderItem = $order->items()->first();
        $this->assertNull($orderItem->service_category_id);
    }
}
