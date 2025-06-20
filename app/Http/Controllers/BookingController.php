<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Fare;
use App\Models\Booking;
use App\Models\Seat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class BookingController extends Controller
{
    /**
     * Display the seat selection page for 'Penumpang' tickets.
     * This method will receive schedule_id and quantities from the homepage.
     */
    public function selectSeats(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'quantities' => 'required|json',
        ]);

        $scheduleId = $request->input('schedule_id');
        $quantities = json_decode($request->input('quantities'), true);

        $schedule = Schedule::with(['ferry', 'originCity', 'destinationCity', 'fares.seatType'])->findOrFail($scheduleId);

        // Filter fares yang dipilih dan pastikan hanya 'Dewasa'
        $selectedFares = $schedule->fares->filter(function ($fare) use ($quantities) {
            return isset($quantities[$fare->id]) &&
                   $fare->seatType->name === 'Dewasa';
        })->map(function ($fare) use ($quantities) {
            $fare->selected_quantity = $quantities[$fare->id];
            return $fare;
        });

        // Hitung total tiket 'Dewasa' yang dibutuhkan dari input form
        $totalTicketsNeeded = 0;
        foreach ($selectedFares as $fare) {
            $totalTicketsNeeded += $fare->selected_quantity;
        }

        // --- MENGAMBIL DATA KURSI DARI TABEL 'seats' ---
        // Mengambil semua kursi untuk jadwal ini dari tabel 'seats'
        $allSeatsForSchedule = Seat::where('schedule_id', $scheduleId)->get();

        // Mengambil total jumlah kursi yang ada di tabel 'seats' untuk jadwal ini
        $ferryTotalSeats = $allSeatsForSchedule->count();

        // Mengambil nomor kursi yang tidak tersedia (is_available = 0)
        $unavailableSeatNumbers = $allSeatsForSchedule->where('is_available', 0)->pluck('seat_number')->toArray();

        // Mengirimkan data yang dibutuhkan ke view
        return view('pilihkursi', compact('schedule', 'selectedFares', 'totalTicketsNeeded', 'ferryTotalSeats', 'unavailableSeatNumbers'));
    }

    /**
     * Display the order detail page for 'Kendaraan' and 'Kamar VIP' tickets.
     * This method will receive schedule_id and quantities from the homepage.
     */
    public function showOrderDetail(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'quantities' => 'required|json',
            'ticket_type' => 'required|string', // 'Kendaraan' or 'Kamar VIP'
            'selected_seat_numbers' => 'nullable|json', // Tambahkan validasi ini
        ]);

        $scheduleId = $request->input('schedule_id');
        $quantities = json_decode($request->input('quantities'), true);
        $ticketType = $request->input('ticket_type');
        $selectedSeatNumbers = json_decode($request->input('selected_seat_numbers', '[]'), true); // Ambil kursi terpilih jika ada

        $schedule = Schedule::with(['ferry', 'originCity', 'destinationCity', 'fares.seatType'])->findOrFail($scheduleId);

        $selectedFares = $schedule->fares->filter(function ($fare) use ($quantities, $ticketType) {
            if (!isset($quantities[$fare->id]) || $quantities[$fare->id] <= 0 || !$fare->seatType) {
                return false;
            }

            if ($ticketType === 'Kendaraan') {
                return Str::contains($fare->seatType->name, 'Kendaraan');
            } elseif ($ticketType === 'Kamar VIP') {
                return Str::contains(Str::lower($fare->seatType->name), 'vip');
            } elseif ($ticketType === 'Dewasa') {
                return Str::contains(Str::lower($fare->seatType->name), 'dewasa');
            }
                        
            return false;
        })->map(function ($fare) use ($quantities) {
            $fare->selected_quantity = $quantities[$fare->id];
            return $fare;
        });

        if ($selectedFares->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada tiket yang valid dipilih.');
        }

        $totalAmount = 0;
        foreach ($selectedFares as $fare) {
            $totalAmount += $fare->price * $fare->selected_quantity;
        }

        // Untuk menampilkan nama pemesan, ambil dari user yang sedang login
        $userName = Auth::user()->name;
        $userEmail = Auth::user()->email;

        return view('detail_pemesanan', compact('schedule', 'selectedFares', 'totalAmount', 'ticketType', 'selectedSeatNumbers', 'userName', 'userEmail'));
    }

    /**
     * Display the payment method selection page.
     */
    public function showPaymentMethods(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'quantities' => 'required|json',
            'ticket_type' => 'required|string',
            'selected_seat_numbers' => 'nullable|json',
            'vip_room_details' => 'array|nullable',
            // 'vip_room_details.*.preference' => 'nullable|string|max:255',
        ]);

        // Pass all data needed for processPayment to the payment method selection view
        // $scheduleId = $request->input('schedule_id');
        // $quantities = $request->input('quantities');
        // $ticketType = $request->input('ticket_type');
        // $selectedSeatNumbers = $request->input('selected_seat_numbers');
        $vipRoomDetails = $request->input('vip_room_details', []);
        $scheduleId = $request->input('schedule_id');
        $quantities = $request->input('quantities');
        $ticketType = $request->input('ticket_type');
        $selectedSeatNumbers = json_decode($request->input('selected_seat_numbers', '[]'), true); // Ambil kursi terpilih jika ada

        $schedule = Schedule::with(['ferry', 'originCity', 'destinationCity', 'fares.seatType'])->findOrFail($scheduleId);


        // $schedule = Schedule::with(['fares.seatType'])->findOrFail($scheduleId);
        $totalAmount = 0;

        foreach (json_decode($quantities, true) as $fareId => $quantity) {
            $fare = $schedule->fares->firstWhere('id', $fareId);
            if (!$fare || $quantity <= 0) {
                return back()->withInput()->with('error', 'Invalid fare...');
            }
            $totalAmount += $fare->price * $quantity;
        }

        return view('payment_method_selection', compact('schedule', 'quantities', 'ticketType', 'selectedSeatNumbers', 'vipRoomDetails', 'totalAmount'));
    }

    /**
     * Process the payment for a booking.
     * This would typically involve a payment gateway integration.
     */
    public function processPayment(Request $request)
{
    $request->validate([
        'schedule_id' => 'required|exists:schedules,id',
        'quantities' => 'required|json',
        'ticket_type' => 'required|string',
        'payment_method' => 'required|string',
        'selected_seat_numbers' => 'required_if:ticket_type,Dewasa|json',
        'passenger_names' => 'nullable|array',
    ]);

    $scheduleId = $request->input('schedule_id');
    $quantities = json_decode($request->input('quantities'), true);
    $ticketType = $request->input('ticket_type');
    $selectedSeatNumbers = json_decode($request->input('selected_seat_numbers', '[]'), true);
    $passengerNames = $request->input('passenger_names', []); // Ambil array nama penumpang

    DB::beginTransaction();

    $schedule = Schedule::with(['fares.seatType'])->findOrFail($scheduleId);
    $totalAmount = 0;

    foreach ($quantities as $fareId => $quantity) {
        $fare = $schedule->fares->firstWhere('id', $fareId);

        if (!$fare || $quantity <= 0) {
            abort(400, "Invalid fare or quantity provided.");
        }

        $totalAmount += $fare->price * $quantity;
    }

    if ($ticketType === 'Dewasa') {
        $unavailableSeats = Seat::where('schedule_id', $scheduleId)
            ->where('is_available', 0)
            ->pluck('seat_number')
            ->toArray();

        foreach ($selectedSeatNumbers as $seatNumber) {
            if (in_array($seatNumber, $unavailableSeats)) {
                abort(400, "Kursi nomor {$seatNumber} sudah tidak tersedia.");
            }
        }
    }

    // Bangun ringkasan penumpang & lainnya
    $summary = [];

    foreach ($quantities as $fareId => $quantity) {
        Log::info("Looping fare_id: $fareId, quantity: $quantity");
    
        $fare = $schedule->fares->firstWhere('id', $fareId);
    
        if (!$fare) {
            Log::warning("FARE ID $fareId tidak ditemukan di schedule.");
            continue;
        }
    
        if (!$fare->seatType) {
            Log::warning("FARE ID $fareId tidak punya seatType.");
            continue;
        }
    
        $seatTypeName = $fare->seatType->name;
    
        Log::info("Seat type ditemukan: $seatTypeName");
    
        if ($seatTypeName === 'Dewasa') {
            for ($i = 0; $i < $quantity; $i++) {
                $summary[] = [
                    'type' => 'Dewasa',
                    'name' => $passengerNames[$i] ?? 'Penumpang ' . ($i + 1),
                    'seat_number' => $selectedSeatNumbers[$i] ?? '-',
                    'quantity' => 1,
                    'price_per_unit' => $fare->price,
                ];
            }
        } elseif ($seatTypeName === 'VIP') {
            $summary[] = [
                'type' => 'Kamar VIP',
                'seat_type' => $seatTypeName,
                'quantity' => $quantity,
                'price_per_unit' => $fare->price,
            ];
        } elseif (str_contains(strtolower($seatTypeName), 'kendaraan')) {
            $summary[] = [
                'type' => 'Kendaraan',
                'seat_type' => $seatTypeName,
                'quantity' => $quantity,
                'price_per_unit' => $fare->price,
            ];
        }        
    }
    
    

    // Simpan booking
    $booking = Booking::create([
        'user_id' => Auth::id(),
        'schedule_id' => $scheduleId,
        'booking_date' => now(),
        'total_amount' => $totalAmount,
        'status' => 'Confirmed',
        'selected_seat_numbers_json' => $summary,
    ]);

    if ($ticketType === 'Dewasa') {
        Seat::where('schedule_id', $scheduleId)
            ->whereIn('seat_number', $selectedSeatNumbers)
            ->update(['is_available' => 0]);
    }

    $booking->status = 'completed';
    $booking->save();

    DB::commit();

    Log::info('Summary booking:', $summary);


    return redirect()->route('booking.success', ['booking_id' => $booking->id])
        ->with('success', 'Pemesanan berhasil!');
}



    public function bookingSuccess(Request $request)
    {
        $booking = Booking::with([
            'schedule.ferry',
            'schedule.originCity',
            'schedule.destinationCity',
            'bookingDetails.seatType',
        'bookingDetails.seat'
            // 'bookingDetails.fare.seatType' // Muat jika Anda memiliki BookingDetail dan ingin menampilkan detail per item tiket
        ])->findOrFail($request->booking_id);

        $bookedSeatNumbersForDisplay = [];
        // Jika Anda menyimpan selected_seat_numbers_json di tabel 'bookings':
        if ($booking->selected_seat_numbers_json) {
            $bookedSeatNumbersForDisplay = $booking->selected_seat_numbers_json ?? [];
        }
        $vip = collect($booking->selected_seat_numbers_json)->filter(fn($item) => $item['type'] === 'Kamar VIP')->values();
        $kendaraan = collect($booking->selected_seat_numbers_json)->filter(fn($item) => $item['type'] === 'Kendaraan')->values();

        // Jika Anda menggunakan BookingDetail dan menyimpan seat_number di sana:
        // if ($booking->bookingDetails->isNotEmpty()) {
        //     foreach ($booking->bookingDetails as $detail) {
        //         if ($detail->seat_number) {
        //             $bookedSeatNumbersForDisplay[] = $detail->seat_number;
        //         }
        //     }
        // }

        return view('booking_success', compact('booking', 'bookedSeatNumbersForDisplay', 'vip', 'kendaraan'));
    }
}