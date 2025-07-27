<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Location;
use Carbon\Carbon;
use Tests\TestCase;

class GuestBookingDuplicateTest extends TestCase
{


    public function test_duplicate_appointment_is_prevented_for_guest()
    {
        // Create test data
        $location = Location::factory()->create();
        $service = Service::factory()->create(['duration' => 60]);
        $staff = Staff::factory()->create();

        // Create a client
        $client = Client::factory()->create([
            'email' => 'test@example.com',
            'is_guest' => true
        ]);

        // Create an existing appointment
        $existingAppointment = Appointment::factory()->create([
            'client_id' => $client->id,
            'staff_id' => $staff->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(11, 0),
            'status' => 'scheduled'
        ]);

        // Attach service to appointment
        $existingAppointment->services()->attach($service->id, [
            'price' => $service->price,
            'duration' => $service->duration
        ]);

        // Attempt to create duplicate appointment
        $response = $this->postJson('/api/guest/book', [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'location_id' => $location->id,
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'time' => '10:00',
            'first_name' => $client->first_name,
            'last_name' => $client->last_name,
            'email' => $client->email,
            'phone' => '1234567890',
            'notes' => 'Test appointment'
        ]);

        // Assert response indicates duplicate
        $response->assertStatus(409);
        $response->assertJson([
            'success' => false,
            'duplicate' => true,
            'message' => 'You already have an appointment for this service on '
        ]);

        // Assert no new appointment was created
        $this->assertCount(1, Appointment::all());
    }

    public function test_non_duplicate_appointment_is_allowed()
    {
        // Create test data
        $location = Location::factory()->create();
        $service = Service::factory()->create(['duration' => 60]);
        $staff = Staff::factory()->create();

        // Create a client
        $client = Client::factory()->create([
            'email' => 'test@example.com',
            'is_guest' => true
        ]);

        // Create an existing appointment for different time
        $existingAppointment = Appointment::factory()->create([
            'client_id' => $client->id,
            'staff_id' => $staff->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(11, 0),
            'status' => 'scheduled'
        ]);

        // Attach service to appointment
        $existingAppointment->services()->attach($service->id, [
            'price' => $service->price,
            'duration' => $service->duration
        ]);

        // Attempt to create appointment for different time
        $response = $this->postJson('/api/guest/book', [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'location_id' => $location->id,
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'time' => '14:00', // Different time
            'first_name' => $client->first_name,
            'last_name' => $client->last_name,
            'email' => $client->email,
            'phone' => '1234567890',
            'notes' => 'Test appointment'
        ]);

        // Assert response indicates success
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        // Assert new appointment was created
        $this->assertCount(2, Appointment::all());
    }

    public function test_cancelled_appointment_allows_new_booking()
    {
        // Create test data
        $location = Location::factory()->create();
        $service = Service::factory()->create(['duration' => 60]);
        $staff = Staff::factory()->create();

        // Create a client
        $client = Client::factory()->create([
            'email' => 'test@example.com',
            'is_guest' => true
        ]);

        // Create a cancelled appointment
        $cancelledAppointment = Appointment::factory()->create([
            'client_id' => $client->id,
            'staff_id' => $staff->id,
            'start_time' => Carbon::tomorrow()->setTime(10, 0),
            'end_time' => Carbon::tomorrow()->setTime(11, 0),
            'status' => 'cancelled'
        ]);

        // Attach service to appointment
        $cancelledAppointment->services()->attach($service->id, [
            'price' => $service->price,
            'duration' => $service->duration
        ]);

        // Attempt to create new appointment for same time
        $response = $this->postJson('/api/guest/book', [
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'location_id' => $location->id,
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'time' => '10:00',
            'first_name' => $client->first_name,
            'last_name' => $client->last_name,
            'email' => $client->email,
            'phone' => '1234567890',
            'notes' => 'Test appointment'
        ]);

        // Assert response indicates success
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        // Assert new appointment was created
        $this->assertCount(2, Appointment::all());
    }
}
