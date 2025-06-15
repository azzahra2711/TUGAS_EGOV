<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Panggil semua seeder yang telah dibuat
        $this->call([
            UserSeeder::class,
            TicketSeeder::class,
            CitySeeder::class,
            FerrySeeder::class,
            SeatTypeSeeder::class,
            ScheduleSeeder::class,
            FareSeeder::class,
            SeatSeeder::class,
            BookingSeeder::class,
            BookingDetailSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}

