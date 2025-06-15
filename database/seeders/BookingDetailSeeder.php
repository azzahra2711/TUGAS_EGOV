<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Seat;
use App\Models\SeatType;
use App\Models\Fare;

class BookingDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookings = Booking::all();
        $seatTypes = SeatType::all();

        foreach ($bookings as $booking) {
            $totalAmount = 0;
            // Setiap booking memiliki 1 sampai 3 jenis detail booking
            $numDetails = rand(1, 3);
            for ($i = 0; $i < $numDetails; $i++) {
                $seatType = $seatTypes->random();
                $quantity = rand(1, 3); // Jumlah tiket per jenis

                // Ambil harga dari tabel fares
                $fare = Fare::where('schedule_id', $booking->schedule_id)
                            ->where('seat_type_id', $seatType->id)
                            ->first();

                $pricePerUnit = $fare ? $fare->price : 0;

                $seat = null;
                // Jika itu jenis penumpang (bukan kendaraan/VIP), coba alokasikan kursi
                if ($seatType->description === 'Ekonomi') {
                    $availableSeat = Seat::where('schedule_id', $booking->schedule_id)
                                         ->where('is_available', true)
                                         ->inRandomOrder()
                                         ->first();
                    if ($availableSeat) {
                        $seat = $availableSeat;
                        $availableSeat->update(['is_available' => false]); // Tandai kursi sebagai tidak tersedia
                    }
                }

                if ($pricePerUnit > 0) {
                    BookingDetail::create([
                        'booking_id' => $booking->id,
                        'seat_id' => $seat ? $seat->id : null,
                        'seat_type_id' => $seatType->id,
                        'quantity' => $quantity,
                        'price_per_unit' => $pricePerUnit,
                    ]);
                    $totalAmount += ($pricePerUnit * $quantity);
                }
            }
            // Perbarui total_amount di booking setelah semua detail ditambahkan
            $booking->update(['total_amount' => $totalAmount]);
        }
    }
}

