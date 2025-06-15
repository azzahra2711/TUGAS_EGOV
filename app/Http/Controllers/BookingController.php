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
            // Tambahkan validasi untuk vehicle_details atau vip_room_details jika ada
            'vip_room_details' => 'array|nullable',
            'vip_room_details.*.preference' => 'nullable|string|max:255',
        ]);

        // Pass all data needed for processPayment to the payment method selection view
        $scheduleId = $request->input('schedule_id');
        $quantities = $request->input('quantities');
        $ticketType = $request->input('ticket_type');
        $selectedSeatNumbers = $request->input('selected_seat_numbers');
        $vipRoomDetails = $request->input('vip_room_details', []);


        $schedule = Schedule::with(['fares.seatType'])->findOrFail($scheduleId);
        $totalAmount = 0;

        foreach (json_decode($quantities, true) as $fareId => $quantity) {
            $fare = $schedule->fares->firstWhere('id', $fareId);
            if (!$fare || $quantity <= 0) {
                return redirect()->back()->with('error', 'Invalid fare or quantity provided during payment method selection.');
            }
            $totalAmount += $fare->price * $quantity;
        }

        return view('payment_method_selection', compact('scheduleId', 'quantities', 'ticketType', 'selectedSeatNumbers', 'vipRoomDetails', 'totalAmount'));
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
            'payment_token' => 'required|string', // Ini bisa berupa dummy atau token dari gateway
            'selected_seat_numbers' => 'required_if:ticket_type,Penumpang|json', // Validasi untuk kursi terpilih
            'vip_room_details' => 'array|nullable',
            'vip_room_details.*.preference' => 'nullable|string|max:255',
        ]);

        $scheduleId = $request->input('schedule_id');
        $quantities = json_decode($request->input('quantities'), true);
        $ticketType = $request->input('ticket_type');
        $selectedSeatNumbers = json_decode($request->input('selected_seat_numbers', '[]'), true); // Ambil kursi terpilih

        DB::beginTransaction();
        try {
            $schedule = Schedule::with(['fares.seatType'])->findOrFail($scheduleId);
            $totalAmount = 0;

            foreach ($quantities as $fareId => $quantity) {
                $fare = $schedule->fares->firstWhere('id', $fareId);

                if (!$fare || $quantity <= 0) {
                    throw new \Exception("Invalid fare or quantity provided.");
                }

                $totalAmount += $fare->price * $quantity;
            }

            // --- Pengecekan Ketersediaan Kursi Final (Penting untuk hindari double-booking) ---
            if ($ticketType === 'Penumpang') {
                $currentUnavailableSeatNumbers = Seat::where('schedule_id', $scheduleId)
                                                    ->where('is_available', 0)
                                                    ->pluck('seat_number')
                                                    ->toArray();

                foreach ($selectedSeatNumbers as $seatNumber) {
                    if (in_array($seatNumber, $currentUnavailableSeatNumbers)) {
                        throw new \Exception("Kursi nomor " . $seatNumber . " sudah tidak tersedia. Silakan pilih kursi lain.");
                    }
                }
            }

            // Buat record booking utama
            $booking = Booking::create([
                'user_id' => Auth::id(),
                'schedule_id' => $scheduleId,
                'total_amount' => $totalAmount,
                'booking_date' => Carbon::now(),
                'status' => 'pending',
                // Simpan nomor kursi terpilih jika diperlukan untuk tampilan sukses
                'selected_seat_numbers_json' => ($ticketType === 'Penumpang' ? json_encode($selectedSeatNumbers) : null),
                // Jika ingin menyimpan metode pembayaran:
                // 'payment_method' => $request->input('payment_method'),
            ]);

            // Handle detail tambahan berdasarkan jenis tiket
            if ($ticketType === 'Penumpang') {
                // Update status ketersediaan kursi di tabel 'seats' ke 0 (tidak tersedia)
                Seat::where('schedule_id', $scheduleId)
                    ->whereIn('seat_number', $selectedSeatNumbers)
                    ->update(['is_available' => 0]);

                // *** CATATAN: Untuk menyimpan nama penumpang dan kursi secara spesifik,
                // Anda perlu membuat entri di tabel booking_details (atau passengers)
                // dengan kolom seperti 'passenger_name' dan 'seat_number'.
                // Contoh (jika Anda punya model BookingDetail dengan 'fare_id', 'quantity', 'price', 'seat_number', 'passenger_name'):
                // $fareIdForPassengerTicket = $selectedFares->where('seatType.name', 'Dewasa')->first()->id ?? null;
                // $farePriceForPassengerTicket = $selectedFares->where('seatType.name', 'Dewasa')->first()->price ?? 0;
                // foreach ($selectedSeatNumbers as $index => $seatNumber) {
                //     $booking->bookingDetails()->create([
                //         'fare_id' => $fareIdForPassengerTicket,
                //         'quantity' => 1,
                //         'price' => $farePriceForPassengerTicket,
                //         'seat_number' => $seatNumber,
                //         'passenger_name' => 'Penumpang ' . ($index + 1), // Ini placeholder, Anda perlu UI untuk input nama
                //     ]);
                // }

            } elseif ($ticketType === 'Kamar VIP') {
                $vipRoomDetails = $request->input('vip_room_details', []);
                foreach ($vipRoomDetails as $detail) {
                    // $booking->vipRooms()->create($detail); // Sesuaikan dengan model Anda jika ada
                }
            }

            // Simulasikan pembayaran berhasil
            $booking->status = 'completed';
            $booking->save();

            DB::commit();

            return redirect()->route('booking.success', ['booking_id' => $booking->id])->with('success', 'Pemesanan berhasil!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput($request->all())->with('error', 'Pemesanan gagal: ' . $e->getMessage());
        }
    }

    public function bookingSuccess(Request $request)
    {
        $booking = Booking::with([
            'schedule.ferry',
            'schedule.originCity',
            'schedule.destinationCity',
            // 'bookingDetails.fare.seatType' // Muat jika Anda memiliki BookingDetail dan ingin menampilkan detail per item tiket
        ])->findOrFail($request->booking_id);

        $bookedSeatNumbersForDisplay = [];
        // Jika Anda menyimpan selected_seat_numbers_json di tabel 'bookings':
        if ($booking->selected_seat_numbers_json) {
            $bookedSeatNumbersForDisplay = json_decode($booking->selected_seat_numbers_json, true);
        }
        // Jika Anda menggunakan BookingDetail dan menyimpan seat_number di sana:
        // if ($booking->bookingDetails->isNotEmpty()) {
        //     foreach ($booking->bookingDetails as $detail) {
        //         if ($detail->seat_number) {
        //             $bookedSeatNumbersForDisplay[] = $detail->seat_number;
        //         }
        //     }
        // }

        return view('booking_success', compact('booking', 'bookedSeatNumbersForDisplay'));
    }
}