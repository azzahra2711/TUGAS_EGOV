<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SeatType;

class SeatTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seatTypes = [
            ['name' => 'Dewasa', 'description' => 'Ekonomi'],
            ['name' => 'VIP', 'description' => 'Kamar VIP'],
            ['name' => 'Kendaraan Roda 2', 'description' => 'Kendaraan'],
            ['name' => 'Kendaraan Roda 4', 'description' => 'Kendaraan'],
        ];

        foreach ($seatTypes as $seatType) {
            SeatType::create($seatType);
        }
    }
}

