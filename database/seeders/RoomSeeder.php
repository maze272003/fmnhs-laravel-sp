<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            ['name' => 'Room 101', 'building' => 'Main Building', 'capacity' => 40, 'is_available' => true],
            ['name' => 'Room 102', 'building' => 'Main Building', 'capacity' => 40, 'is_available' => true],
            ['name' => 'Room 103', 'building' => 'Main Building', 'capacity' => 35, 'is_available' => true],
            ['name' => 'Room 201', 'building' => 'Main Building', 'capacity' => 40, 'is_available' => true],
            ['name' => 'Room 202', 'building' => 'Main Building', 'capacity' => 40, 'is_available' => true],
            ['name' => 'Computer Lab 1', 'building' => 'Science Building', 'capacity' => 30, 'is_available' => true],
            ['name' => 'Computer Lab 2', 'building' => 'Science Building', 'capacity' => 30, 'is_available' => true],
            ['name' => 'Science Lab', 'building' => 'Science Building', 'capacity' => 35, 'is_available' => true],
            ['name' => 'Library', 'building' => 'Main Building', 'capacity' => 50, 'is_available' => true],
            ['name' => 'AVR', 'building' => 'Main Building', 'capacity' => 100, 'is_available' => true],
        ];

        foreach ($rooms as $room) {
            Room::create($room);
        }
    }
}
