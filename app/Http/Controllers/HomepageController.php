<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\City;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Str;

class HomepageController extends Controller
{
    /**
     * Menampilkan halaman beranda dengan data awal atau hasil filter.
     */
    public function index(Request $request)
    {
        // Ambil data kota dan jenis tiket dari database untuk dropdown filter
        $cities = City::orderBy('name')->get();
        $ticketTypes = Ticket::orderBy('name')->get();

        // Query dasar untuk jadwal dengan eager loading relasi yang dibutuhkan
        $query = Schedule::with(['ferry', 'originCity', 'destinationCity', 'fares.seatType']);

        // Logika filtering untuk jenis-tiket
        if ($request->has('jenis-tiket') && $request->input('jenis-tiket') != '') {
            $selectedTicketTypeName = $request->input('jenis-tiket');

            // Apply filter based on the selected ticket type to the schedule's fares.
            // This ensures only schedules that offer the selected ticket type are considered.
            $query->whereHas('fares', function ($fareQuery) use ($selectedTicketTypeName) {
                $fareQuery->whereHas('seatType', function ($seatTypeQuery) use ($selectedTicketTypeName) {
                    if ($selectedTicketTypeName === 'Penumpang') {
                        $seatTypeQuery->whereIn('name', ['Dewasa', 'Anak-anak']);
                    } elseif ($selectedTicketTypeName === 'Kendaraan') {
                        $seatTypeQuery->where('name', 'like', '%Kendaraan%');
                    } elseif ($selectedTicketTypeName === 'Kamar VIP') {
                        $seatTypeQuery->where('name', 'like', '%VIP%');
                    }
                });
            });
        }

        // Logika filtering untuk kota-asal
        if ($request->has('kota-asal') && $request->input('kota-asal') != '') {
            $originCityValue = $request->input('kota-asal');
            $parts = explode('/', $originCityValue);
            $originCityCode = end($parts);

            if (!empty($originCityCode)) {
                $city = City::where('code', $originCityCode)->first();
                if ($city) {
                    $query->where('origin_city_id', $city->id);
                }
            }
        }

        // Logika filtering untuk kota-tujuan
        if ($request->has('kota-tujuan') && $request->input('kota-tujuan') != '') {
            $destinationCityValue = $request->input('kota-tujuan');
            $parts = explode('/', $destinationCityValue);
            $destinationCityCode = end($parts);

            if (!empty($destinationCityCode)) {
                $city = City::where('code', $destinationCityCode)->first();
                if ($city) {
                    $query->where('destination_city_id', $city->id);
                }
            }
        }

        // Logika filtering untuk tanggal-berangkat
        if ($request->has('tanggal-berangkat') && $request->input('tanggal-berangkat') != '') {
            $departureDate = $request->input('tanggal-berangkat');
            $query->whereDate('departure_date', $departureDate);
        } else {
            // Default to today and 10 days ahead if no date filter is applied
            $today = Carbon::now()->startOfDay();
            $tenDaysLater = Carbon::now()->addDays(10)->endOfDay();
            $query->whereBetween('departure_date', [$today, $tenDaysLater]);
        }

        // Ambil hasil jadwal
        $schedules = $query->distinct()->orderBy('departure_date')->orderBy('departure_time')->get();

        // Pass the request object to the view so it can be used for conditional display of fares
        return view('homepage', compact('schedules', 'cities', 'ticketTypes', 'request'));
    }
}