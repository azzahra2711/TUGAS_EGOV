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
    public function selectSeats(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'quantities' => 'required|json',
        ]);

        $scheduleId = $request->input('schedule_id');
        $quantities = json_decode($request->input('quantities'), true);

        $schedule = Schedule::with(['ferry', 'originCity', 'destinationCity', 'fares.seatType'])->findOrFail($scheduleId);

        $selectedFares = $schedule->fares->filter(function ($fare) use ($quantities) {
            return isset($quantities[$fare->id]) &&
                   $fare->seatType && 
                   $fare->seatType->name === 'Dewasa';
        })->map(function ($fare) use ($quantities) {
            $fare->selected_quantity = $quantities[$fare->id];
            return $fare;
        });

        $totalTicketsNeeded = $selectedFares->sum('selected_quantity');
        $allSeatsForSchedule = Seat::where('schedule_id', $scheduleId)->get();
        $ferryTotalSeats = $allSeatsForSchedule->count();
        $unavailableSeatNumbers = $allSeatsForSchedule->where('is_available', 0)->pluck('seat_number')->toArray();

        return view('pilihkursi', compact('schedule', 'selectedFares', 'totalTicketsNeeded', 'ferryTotalSeats', 'unavailableSeatNumbers'));
    }

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

        $quantitiesArray = json_decode($validated['quantities'], true);
        $selectedSeatNumbersArray = json_decode($validated['selected_seat_numbers'] ?? '[]', true);

        $schedule = Schedule::with(['ferry', 'originCity', 'destinationCity', 'fares.seatType'])->findOrFail($validated['schedule_id']);

        $selectedFares = $schedule->fares->filter(function ($fare) use ($quantitiesArray, $validated) {
            if (!isset($quantitiesArray[$fare->id]) || $quantitiesArray[$fare->id] <= 0 || !$fare->seatType) {
                return false;
            }

            if ($validated['ticket_type'] === 'Kendaraan') {
                return Str::contains($fare->seatType->name, 'Kendaraan');
            } elseif ($validated['ticket_type'] === 'Kamar VIP') {
                return Str::contains(Str::lower($fare->seatType->name), 'vip');
            } elseif ($validated['ticket_type'] === 'Penumpang') {
                return $fare->seatType->name === 'Dewasa'; 
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

    public function showPaymentMethods(Request $request)
    {
        $scheduleId = $request->input('schedule_id');
        $quantities = json_decode($request->input('quantities'), true);
        $selectedSeatNumbers = json_decode($request->input('selected_seat_numbers'), true);

        $schedule = Schedule::with(['originCity', 'destinationCity'])->findOrFail($scheduleId);

        $selectedFares = Fare::whereIn('id', array_keys($quantities))->get();
        foreach ($selectedFares as $fare) {
            $fare->selected_quantity = $quantities[$fare->id];
        }

        $totalAmount = $selectedFares->sum(function ($fare) {
            return $fare->price * $fare->selected_quantity;
        });

        // Simpan ke session untuk tahap pembayaran
        session([
            'booking' => [
                'schedule_id' => $scheduleId,
                'ticket_type' => 'Penumpang', // default, sesuaikan jika ada tipe lain
                'quantities' => json_encode($quantities),
                'selected_seat_numbers' => json_encode($selectedSeatNumbers),
                'total_amount' => $totalAmount,
            ]
        ]);

        return view('payment_method_selection', [
            'schedule' => $schedule,
            'selectedFares' => $selectedFares,
            'selectedSeatNumbersArray' => $selectedSeatNumbers,
            'totalAmount' => $totalAmount,
            'userName' => Auth::user()->name ?? 'Pengguna',
            'userEmail' => Auth::user()->email ?? 'pengguna@example.com',
        ]);
    }

    public function processPayment(Request $request)
    {
        $data = session('booking');

        if (!$data) {
            return redirect()->route('homepage')->with('error', 'Data pemesanan tidak ditemukan. Silakan mulai ulang pemesanan.');
        }

        if (!isset($data['schedule_id'], $data['ticket_type'], $data['quantities'], $data['total_amount'])) {
            return redirect()->back()->with('error', 'Data pemesanan tidak lengkap.');
        }

        $scheduleId = $data['schedule_id'];
        $quantities = json_decode($data['quantities'], true);
        $ticketType = $data['ticket_type'];
        $selectedSeatNumbers = json_decode($data['selected_seat_numbers'] ?? '[]', true);
        $totalAmount = $data['total_amount'];
        $paymentMethod = $request->input('payment_method') ?? 'transfer'; // asumsi default

        DB::beginTransaction();
        try {
            if ($ticketType === 'Penumpang') {
                $unavailable = Seat::where('schedule_id', $scheduleId)
                                   ->where('is_available', 0)
                                   ->pluck('seat_number')->toArray();
                foreach ($selectedSeatNumbers as $seatNumber) {
                    if (in_array($seatNumber, $unavailable)) {
                        throw new \Exception("Kursi nomor $seatNumber tidak tersedia.");
                    }
                }
            }

            $booking = Booking::create([
                'user_id' => Auth::id(),
                'schedule_id' => $scheduleId,
                'total_amount' => $totalAmount,
                'booking_date' => Carbon::now(),
                'status' => 'completed',
                'payment_method' => $paymentMethod,
                'selected_seat_numbers_json' => json_encode($selectedSeatNumbers),
            ]);

            if ($ticketType === 'Penumpang') {
                Seat::where('schedule_id', $scheduleId)
                    ->whereIn('seat_number', $selectedSeatNumbers)
                    ->update(['is_available' => 0]);
            }

            DB::commit();

            session()->forget('booking');
            return redirect()->route('booking.success', ['booking_id' => $booking->id])
                             ->with('success', 'Pemesanan berhasil!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Pemesanan gagal: ' . $e->getMessage());
        }
    }

    public function bookingSuccess(Request $request)
    {
        $booking = Booking::with([
            'schedule.ferry',
            'schedule.originCity',
            'schedule.destinationCity',
        ])->findOrFail($request->booking_id);

        $bookedSeatNumbersForDisplay = [];
        if ($booking->selected_seat_numbers_json) {
            $bookedSeatNumbersForDisplay = json_decode($booking->selected_seat_numbers_json, true);
        }

        return view('booking_success', compact('booking', 'bookedSeatNumbersForDisplay'));
    }
}