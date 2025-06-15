<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\User;
use App\Models\Schedule;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $schedules = Schedule::all();

        if ($users->isEmpty() || $schedules->isEmpty()) {
            $this->command->info('Tidak ada user atau jadwal untuk membuat booking.');
            return;
        }

        // Buat beberapa booking dummy
        for ($i = 0; $i < 20; $i++) { // 20 dummy bookings
            $user = $users->random();
            $schedule = $schedules->random();
            $bookingDate = Carbon::now()->subDays(rand(1, 30)); // Booking bisa terjadi di masa lalu

            Booking::create([
                'user_id' => $user->id,
                'schedule_id' => $schedule->id,
                'booking_date' => $bookingDate,
                'total_amount' => 0, // Akan dihitung oleh BookingDetailSeeder
                'status' => ['Pending', 'Confirmed', 'Paid'][array_rand(['Pending', 'Confirmed', 'Paid'])],
            ]);
        }
    }
}

