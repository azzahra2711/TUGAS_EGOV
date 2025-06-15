<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Seat;
use App\Models\Schedule;

class SeatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schedules = Schedule::all();
        foreach ($schedules as $schedule) {
            // Asumsi setiap jadwal memiliki 30 kursi untuk penumpang
            for ($i = 1; $i <= 30; $i++) {
                Seat::create([
                    'schedule_id' => $schedule->id,
                    'seat_number' => $i,
                    'is_available' => true, // Awalnya semua tersedia
                ]);
            }
        }
    }
}

