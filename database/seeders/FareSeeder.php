<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Fare;
use App\Models\Schedule;
use App\Models\SeatType;
use Illuminate\Support\Str;

class FareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schedules = Schedule::all();
        $seatTypes = SeatType::all();

        foreach ($schedules as $schedule) {
            foreach ($seatTypes as $seatType) {
                $price = 0;
                // Logika harga dummy berdasarkan jenis kursi
                if ($seatType->name === 'Dewasa') {
                    $price = rand(300000, 500000);
                } elseif ($seatType->name === 'Anak') {
                    $price = rand(150000, 250000);
                } elseif ($seatType->name === 'Bayi') {
                    $price = rand(50000, 100000);
                } elseif ($seatType->name === 'VIP') {
                    $price = rand(800000, 1500000);
                } elseif (Str::contains($seatType->name, 'Kendaraan')) {
                    $price = rand(1000000, 3000000); // Harga kendaraan lebih tinggi
                }

                if ($price > 0) {
                    Fare::create([
                        'schedule_id' => $schedule->id,
                        'seat_type_id' => $seatType->id,
                        'price' => $price,
                    ]);
                }
            }
        }
    }
}

