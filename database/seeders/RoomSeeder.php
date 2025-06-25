<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            [
                'name' => 'Treatment Room 1',
                'description' => 'Primary treatment room for spa services',
                'room_number' => '101',
                'floor' => 1,
                'capacity' => 2,
                'is_active' => true,
                'room_type' => Room::TYPE_TREATMENT,
                'hourly_rate' => 50.00,
                'daily_rate' => 350.00,
                'features' => ['massage table', 'sink', 'ambient lighting', 'sound system'],
                'notes' => 'Ideal for massage and facial treatments',
                'image_url' => null,
            ],
            [
                'name' => 'Treatment Room 2',
                'description' => 'Secondary treatment room with additional space',
                'room_number' => '102',
                'floor' => 1,
                'capacity' => 3,
                'is_active' => true,
                'room_type' => Room::TYPE_TREATMENT,
                'hourly_rate' => 60.00,
                'daily_rate' => 400.00,
                'features' => ['massage table', 'sink', 'ambient lighting', 'sound system', 'shower'],
                'notes' => 'Suitable for body treatments and wraps',
                'image_url' => null,
            ],
            [
                'name' => 'Salon Station 1',
                'description' => 'Hair styling station',
                'room_number' => '201',
                'floor' => 2,
                'capacity' => 1,
                'is_active' => true,
                'room_type' => Room::TYPE_OTHER,
                'hourly_rate' => 30.00,
                'daily_rate' => 200.00,
                'features' => ['styling chair', 'mirror', 'hair dryer'],
                'notes' => 'Located near the front entrance',
                'image_url' => null,
            ],
            [
                'name' => 'Salon Station 2',
                'description' => 'Hair styling station',
                'room_number' => '202',
                'floor' => 2,
                'capacity' => 1,
                'is_active' => true,
                'room_type' => Room::TYPE_OTHER,
                'hourly_rate' => 30.00,
                'daily_rate' => 200.00,
                'features' => ['styling chair', 'mirror', 'hair dryer'],
                'notes' => 'Located near the back entrance',
                'image_url' => null,
            ],
            [
                'name' => 'Event Space',
                'description' => 'Large room for group events and classes',
                'room_number' => '301',
                'floor' => 3,
                'capacity' => 15,
                'is_active' => true,
                'room_type' => Room::TYPE_EVENT,
                'hourly_rate' => 100.00,
                'daily_rate' => 600.00,
                'features' => ['yoga mats', 'sound system', 'projector', 'refreshment area'],
                'notes' => 'Bookable for private events and workshops',
                'image_url' => null,
            ],
        ];

        foreach ($rooms as $roomData) {
            Room::firstOrCreate(
                ['name' => $roomData['name'], 'room_number' => $roomData['room_number']],
                $roomData
            );
        }

        $this->command->info('Room seeder completed successfully.');
    }
}
