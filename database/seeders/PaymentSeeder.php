<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Booking;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookings = Booking::all();
        $paymentMethods = ['BNI VA', 'BRI VA', 'MANDIRI VA', 'BCA VA'];
        $paymentStatuses = ['Completed', 'Failed', 'Pending'];

        foreach ($bookings as $booking) {
            // Hanya buat payment jika booking memiliki total_amount > 0
            if ($booking->total_amount > 0) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'transaction_id' => 'TRX-' . Carbon::now()->timestamp . '-' . rand(1000, 9999), // ID transaksi unik
                    'amount_paid' => $booking->total_amount,
                    'payment_date' => Carbon::now()->subDays(rand(0, 7)), // Pembayaran bisa terjadi di masa lalu
                    'status' => $paymentStatuses[array_rand($paymentStatuses)],
                ]);
            }
        }
    }
}

