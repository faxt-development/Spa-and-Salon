<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Appointment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ClientReportTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_generate_client_spend_report()
    {
        // Create an admin user
        $admin = $this->createAdminUser();

        // Create a staff member with all required fields
        $staff = Staff::create([
            'first_name' => 'Test',
            'last_name' => 'Staff',
            'email' => 'staff@example.com',
            'phone' => '123-456-7890',
            'position' => 'Stylist',
            'active' => true,
            'work_start_time' => '09:00:00',
            'work_end_time' => '17:00:00',
            'work_days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
        ]);

        // Create a test client
        $client = Client::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'Client',
            'email' => 'test@example.com',
        ]);

        // Create test appointments and payments
        for ($i = 0; $i < 3; $i++) {
            $appointment = Appointment::factory()->create([
                'client_id' => $client->id,
                'staff_id' => $staff->id,
                'status' => 'completed',
                'start_time' => now()->subDays(30 - ($i * 10)),
                'end_time' => now()->subDays(30 - ($i * 10))->addHour(),
            ]);

            $order = Order::factory()->create([
                'client_id' => $client->id,
                'status' => 'completed',
                'subtotal' => 10000, // $100.00
                'tax_amount' => 0, // $0.00
                'total_amount' => 10000, // $100.00
                'created_at' => $appointment->start_time,
            ]);

            Payment::factory()->create([
                'appointment_id' => $appointment->id,
                'order_id' => $order->id,
                'amount' => 10000, // $100.00
                'status' => 'completed',
                'payment_method' => 'credit_card',
                'transaction_id' => 'test_' . uniqid(),
                'created_at' => $appointment->start_time,
                'updated_at' => $appointment->start_time,
            ]);
        }

        // Test the index page
        $response = $this->actingAs($admin)
            ->get(route('reports.clients.index'));

        $response->assertStatus(200);
        $response->assertSee('Client Spend Analytics');
        $response->assertSee('$300.00'); // Total spend

        // Test the export functionality
        $exportResponse = $this->actingAs($admin)
            ->get(route('reports.clients.export'));

        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Test single client export
        $singleExportResponse = $this->actingAs($admin)
            ->get(route('reports.clients.export.single', $client));

        $singleExportResponse->assertStatus(200);
        $singleExportResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    protected function createAdminUser()
{
    // Create or get the admin role
    $role = \Spatie\Permission\Models\Role::firstOrCreate(
        ['name' => 'admin'],
        ['guard_name' => 'web']
    );

    // Create a unique email for each test run
    $email = 'testadmin_' . uniqid() . '@example.com';

    // Create the user
    $user = \App\Models\User::factory()->create([
        'name' => 'Admin User',
        'email' => $email,
        'password' => bcrypt('password'),
    ]);

    // Assign the role
    $user->assignRole($role);

    return $user;
}
}
