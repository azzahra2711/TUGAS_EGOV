<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\City;
use App\Models\Ferry;
use Carbon\Carbon;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    $cities = City::all();
    $ferries = Ferry::all();

    for ($i = 0; $i < 10; $i++) {
        $departureDate = Carbon::now()->addDays($i)->format('Y-m-d');
        $departureTime = Carbon::createFromTime(rand(4, 10), 0, 0); // Carbon instance

        $originCity = $cities->random();
        $destinationCity = $cities->except($originCity->id)->random();

        $ferry = $ferries->random();

        $travelHours = rand(16, 20);
        $arrivalDateTime = (clone $departureTime)->copy()->addHours($travelHours)->addMinutes(rand(1, 59)); // Hindari 00:00:00

        Schedule::create([
            'ferry_id' => $ferry->id,
            'origin_city_id' => $originCity->id,
            'destination_city_id' => $destinationCity->id,
            'departure_date' => $departureDate,
            'departure_time' => $departureTime->format('H:i:s'),
            'arrival_date' => $arrivalDateTime->format('Y-m-d'),
            'arrival_time' => $arrivalDateTime->format('H:i:s'),
        ]);

        // Jadwal tambahan di hari yang sama
        if (rand(0, 1)) {
            $departureTime2 = Carbon::createFromTime(rand(12, 20), 0, 0);
            $arrivalDateTime2 = (clone $departureTime2)->copy()->addHours(rand(16, 20))->addMinutes(rand(1, 59));

            Schedule::create([
                'ferry_id' => $ferries->random()->id,
                'origin_city_id' => $originCity->id,
                'destination_city_id' => $destinationCity->id,
                'departure_date' => $departureDate,
                'departure_time' => $departureTime2->format('H:i:s'),
                'arrival_date' => $arrivalDateTime2->format('Y-m-d'),
                'arrival_time' => $arrivalDateTime2->format('H:i:s'),
            ]);
        }
    }
}

}

