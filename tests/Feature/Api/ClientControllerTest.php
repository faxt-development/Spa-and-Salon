<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $adminUser;
    protected $staffUser;
    protected $clientUser;
    protected $client;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create users with different roles
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $this->staffUser = User::factory()->create();
        $this->staffUser->assignRole('staff');

        $this->clientUser = User::factory()->create();
        $this->clientUser->assignRole('client');

        // Create a test client
        $this->client = Client::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'Client',
            'email' => 'test@example.com',
            'phone' => '123-456-7890',
            'date_of_birth' => '1990-01-01',
            'address' => '123 Test St',
            'notes' => 'Test notes',
            'marketing_consent' => true
        ]);
    }

    /**
     * Test that admin can access client list.
     */
    public function test_admin_can_access_client_list(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/clients');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'current_page',
                         'data',
                         'first_page_url',
                         'from',
                         'last_page',
                         'last_page_url',
                         'links',
                         'next_page_url',
                         'path',
                         'per_page',
                         'prev_page_url',
                         'to',
                         'total',
                     ]
                 ]);
    }

    /**
     * Test that staff can access client list.
     */
    public function test_staff_can_access_client_list(): void
    {
        Sanctum::actingAs($this->staffUser);

        $response = $this->getJson('/api/clients');

        $response->assertStatus(200);
    }

    /**
     * Test that client users cannot access client list.
     */
    public function test_client_cannot_access_client_list(): void
    {
        Sanctum::actingAs($this->clientUser);

        $response = $this->getJson('/api/clients');

        $response->assertStatus(403);
    }

    /**
     * Test that admin can view a specific client.
     */
    public function test_admin_can_view_client(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson("/api/clients/{$this->client->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'data' => [
                         'id' => $this->client->id,
                         'first_name' => 'Test',
                         'last_name' => 'Client',
                         'email' => 'test@example.com',
                     ]
                 ]);
    }

    /**
     * Test that admin can create a client.
     */
    public function test_admin_can_create_client(): void
    {
        Sanctum::actingAs($this->adminUser);

        $clientData = [
            'first_name' => 'New',
            'last_name' => 'Client',
            'email' => 'new@example.com',
            'phone' => '987-654-3210',
            'date_of_birth' => '1995-05-15',
            'address' => '456 New St',
            'notes' => 'New client notes',
            'marketing_consent' => true
        ];

        $response = $this->postJson('/api/clients', $clientData);

        $response->assertStatus(201)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Client created successfully',
                 ]);

        $this->assertDatabaseHas('clients', [
            'first_name' => 'New',
            'last_name' => 'Client',
            'email' => 'new@example.com',
        ]);
    }

    /**
     * Test that admin can update a client.
     */
    public function test_admin_can_update_client(): void
    {
        Sanctum::actingAs($this->adminUser);

        $updatedData = [
            'first_name' => 'Updated',
            'last_name' => 'Client',
            'email' => 'updated@example.com',
        ];

        $response = $this->putJson("/api/clients/{$this->client->id}", $updatedData);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Client updated successfully',
                 ]);

        $this->assertDatabaseHas('clients', [
            'id' => $this->client->id,
            'first_name' => 'Updated',
            'last_name' => 'Client',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test that admin can delete a client.
     */
    public function test_admin_can_delete_client(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->deleteJson("/api/clients/{$this->client->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Client deleted successfully',
                 ]);

        $this->assertSoftDeleted('clients', [
            'id' => $this->client->id
        ]);
    }

    /**
     * Test validation rules when creating a client.
     */
    public function test_validation_rules_when_creating_client(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/clients', [
            // Missing required fields
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['first_name', 'last_name', 'email']);
    }

    /**
     * Test that admin can retrieve client appointments.
     */
    public function test_admin_can_retrieve_client_appointments(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson("/api/clients/{$this->client->id}/appointments");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data'
                 ]);
    }
}
