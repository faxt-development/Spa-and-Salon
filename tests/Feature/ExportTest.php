<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password')
        ]);
        
        // Create regular user
        $this->user = User::factory()->create([
            'role' => 'user',
            'email' => 'user@example.com',
            'password' => bcrypt('password')
        ]);
        
        // Create test data
        $this->createTestData();
    }
    
    protected function createTestData()
    {
        // Create test services
        $services = Service::factory()->count(3)->create();
        
        // Create test appointments
        Appointment::factory()->count(5)->create();
        
        // Create test orders with items
        Order::factory()
            ->count(3)
            ->hasOrderItems(2, [
                'service_id' => fn() => $services->random()->id,
                'quantity' => 1,
                'price' => 50.00,
            ])
            ->create();
    }
    
    /** @test */
    public function admin_can_export_appointments_to_excel()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('export.excel', 'appointments'));
            
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('appointments_', $response->headers->get('content-disposition'));
    }
    
    /** @test */
    public function admin_can_export_services_to_pdf()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('export.pdf', 'services'));
            
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('services_', $response->headers->get('content-disposition'));
    }
    
    /** @test */
    public function admin_can_preview_orders_pdf()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('export.preview', 'orders'));
            
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
    
    /** @test */
    public function regular_user_cannot_access_export_routes()
    {
        $routes = [
            route('export.excel', 'appointments'),
            route('export.pdf', 'services'),
            route('export.preview', 'orders'),
        ];
        
        foreach ($routes as $route) {
            $response = $this->actingAs($this->user)->get($route);
            $response->assertStatus(403); // Forbidden
        }
    }
    
    /** @test */
    public function guest_cannot_access_export_routes()
    {
        $routes = [
            route('export.excel', 'appointments'),
            route('export.pdf', 'services'),
            route('export.preview', 'orders'),
        ];
        
        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertRedirect(route('login'));
        }
    }
    
    /** @test */
    public function invalid_export_type_returns_404()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('export.excel', 'invalid-type'));
            
        $response->assertStatus(404);
    }
}
