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
                   $fare->seatType && // Tambahkan pengecekan null untuk seatType
                   $fare->seatType->name === 'Dewasa';
        })->map(function ($fare) use ($quantities) {
            $fare->selected_quantity = $quantities[$fare->id];
            return $fare;
        });

        // Hitung total tiket 'Dewasa' yang dibutuhkan dari input form
        $totalTicketsNeeded = $selectedFares->sum('selected_quantity');

        // --- MENGAMBIL DATA KURSI DARI TABEL 'seats' ---
        $allSeatsForSchedule = Seat::where('schedule_id', $scheduleId)->get();

        // Mengambil total jumlah kursi yang ada di tabel 'seats' untuk jadwal ini
        $ferryTotalSeats = $allSeatsForSchedule->count();

        // Mengambil nomor kursi yang tidak tersedia (is_available = 0)
        $unavailableSeatNumbers = $allSeatsForSchedule->where('is_available', 0)->pluck('seat_number')->toArray();

        return view('pilihkursi', compact('schedule', 'selectedFares', 'totalTicketsNeeded', 'ferryTotalSeats', 'unavailableSeatNumbers'));
    }

    /**
     * Display the order detail page (Konfirmasi Pembayaran).
     * This method receives data from pilihkursi.blade.php or homepage.
     */
    public function showOrderDetail(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'ticket_type' => 'required|string',
            'quantities' => 'required|string', // JSON string
            'selected_seat_numbers' => 'nullable|string', // JSON string, for Penumpang
            'vip_room_details' => 'array|nullable', // For Kamar VIP
            'vip_room_details.*.preference' => 'nullable|string|max:255',
        ]);

        // Decode quantities and selected_seat_numbers
        $quantitiesArray = json_decode($validated['quantities'], true);
        $selectedSeatNumbersArray = json_decode($validated['selected_seat_numbers'] ?? '[]', true);

        $schedule = Schedule::with(['ferry', 'originCity', 'destinationCity', 'fares.seatType'])->findOrFail($validated['schedule_id']);

        $selectedFares = $schedule->fares->filter(function ($fare) use ($quantitiesArray, $validated) {
            // Check if fare ID exists in quantities and if it matches ticket_type
            if (!isset($quantitiesArray[$fare->id]) || $quantitiesArray[$fare->id] <= 0 || !$fare->seatType) {
                return false;
            }

            if ($validated['ticket_type'] === 'Kendaraan') {
                return Str::contains($fare->seatType->name, 'Kendaraan');
            } elseif ($validated['ticket_type'] === 'Kamar VIP') {
                return Str::contains(Str::lower($fare->seatType->name), 'vip');
            } elseif ($validated['ticket_type'] === 'Penumpang') {
                return $fare->seatType->name === 'Dewasa'; // Assuming 'Dewasa' is the passenger type
            }
            return false;
        })->map(function ($fare) use ($quantitiesArray) {
            $fare->selected_quantity = $quantitiesArray[$fare->id];
            return $fare;
        });

        if ($selectedFares->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada tiket yang valid dipilih.');
        }

        $totalAmount = 0;
        foreach ($selectedFares as $fare) {
            $totalAmount += $fare->price * $fare->selected_quantity;
        }

        // Store all necessary data in session for the next steps
        session([
            'booking.schedule_id' => $validated['schedule_id'],
            'booking.ticket_type' => $validated['ticket_type'],
            'booking.quantities' => $validated['quantities'], // Keep as JSON string
            'booking.selected_seat_numbers' => $validated['selected_seat_numbers'], // Keep as JSON string
            'booking.vip_room_details' => $validated['vip_room_details'] ?? null,
            'booking.total_amount' => $totalAmount, // Store calculated total amount
        ]);

        $userName = Auth::user()->name;
        $userEmail = Auth::user()->email;

        return view('detail_pemesanan', compact(
            'schedule',
            'selectedFares',
            'totalAmount',
            'selectedSeatNumbersArray', // Pass as array for Blade loop
            'userName',
            'userEmail'
        ));
    }


    /**
     * Display the payment method selection page.
     * This method retrieves data from session.
     */
    public function showPaymentMethods(Request $request)
    {
        $bookingData = session('booking');

        if (!$bookingData) {
            return redirect()->route('homepage')->with('error', 'Data pemesanan tidak ditemukan. Silakan mulai ulang pemesanan.');
        }

        // Validate payment_method if it's coming from a form submission
        // Otherwise, it's just displaying the options
        if ($request->isMethod('post')) {
            $request->validate([
                'payment_method' => 'required|string'
            ]);
            // Store selected payment method in session
            session(['payment_method_selection' => $request->input('payment_method')]);
        }


        // Re-fetch necessary data for the payment method selection view
        $scheduleId = $bookingData['schedule_id'];
        $quantities = $bookingData['quantities']; // This is a JSON string
        $ticketType = $bookingData['ticket_type'];
        $selectedSeatNumbers = $bookingData['selected_seat_numbers']; // This is a JSON string
        $vipRoomDetails = $bookingData['vip_room_details'] ?? [];
        $totalAmount = $bookingData['total_amount']; // Retrieve from session

        return view('payment_method_selection', compact(
            'scheduleId',
            'quantities',
            'ticketType',
            'selectedSeatNumbers',
            'vipRoomDetails',
            'totalAmount'
        ));
    }


    /**
     * Process the payment for a booking.
     * This method retrieves data from session and creates the booking.
     */
    public function processPayment(Request $request)
    {
        $data = session('booking');

        if (!$data) {
            return redirect()->route('homepage')->with('error', 'Data pemesanan tidak ditemukan. Silakan mulai ulang pemesanan.');
        }

        // Validate that essential data exists from session
        if (!isset($data['schedule_id'], $data['ticket_type'], $data['quantities'], $data['payment_method'], $data['total_amount'])) {
            return redirect()->back()->with('error', 'Data pemesanan tidak lengkap. Harap coba lagi.');
        }

        $scheduleId = $data['schedule_id'];
        $quantities = json_decode($data['quantities'], true); // Decode for use
        $ticketType = $data['ticket_type'];
        $selectedSeatNumbers = json_decode($data['selected_seat_numbers'] ?? '[]', true); // Decode for use
        $vipRoomDetails = $data['vip_room_details'] ?? [];
        $totalAmount = $data['total_amount']; // Use total amount from session

        DB::beginTransaction();
        try {
            // Re-check seat availability just before final booking for 'Penumpang'
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

            // Create main booking record
            $booking = Booking::create([
                'user_id' => Auth::id(),
                'schedule_id' => $scheduleId,
                'total_amount' => $totalAmount, // Use the pre-calculated total amount
                'booking_date' => Carbon::now(),
                'status' => 'pending', // Set to pending initially, then completed on success
                'payment_method' => $data['payment_method'], // Store payment method
                'selected_seat_numbers_json' => ($ticketType === 'Penumpang' ? json_encode($selectedSeatNumbers) : null),
                // Assuming payment_token is dummy from payment_method_selection.blade.php
                // In a real app, this would come from the payment gateway's response
                // 'payment_token' => 'dummy_token_123',
            ]);

            // Handle additional details based on ticket type
            if ($ticketType === 'Penumpang') {
                // Update seat availability in 'seats' table
                Seat::where('schedule_id', $scheduleId)
                    ->whereIn('seat_number', $selectedSeatNumbers)
                    ->update(['is_available' => 0]);

                // *** REMINDER: For storing specific passenger names and seats,
                // you would create entries in a booking_details table here.
                // E.g., $booking->bookingDetails()->create([...]);
            } elseif ($ticketType === 'Kamar VIP') {
                foreach ($vipRoomDetails as $detail) {
                    // $booking->vipRooms()->create($detail); // Adjust with your model if exists
                }
            }

            // Simulate successful payment (replace with actual payment gateway callback)
            $booking->status = 'completed';
            $booking->save();

            DB::commit();

            session()->forget('booking'); // Clear session after successful booking

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
            // 'bookingDetails.fare.seatType' // Load if you have BookingDetail and want to display item details
        ])->findOrFail($request->booking_id);

        $bookedSeatNumbersForDisplay = [];
        // If you store selected_seat_numbers_json in the 'bookings' table:
        if ($booking->selected_seat_numbers_json) {
            $bookedSeatNumbersForDisplay = json_decode($booking->selected_seat_numbers_json, true);
        }
        // If you use BookingDetail and store seat_number there:
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