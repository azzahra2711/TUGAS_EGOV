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
        $cities = City::orderBy('name')->get();
        $ticketTypes = Ticket::orderBy('name')->get();

        $query = Schedule::with(['ferry', 'originCity', 'destinationCity', 'fares.seatType']);

        if ($request->has('jenis-tiket') && $request->input('jenis-tiket') != '') {
            $selectedTicketTypeName = $request->input('jenis-tiket');

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

        if ($request->has('tanggal-berangkat') && $request->input('tanggal-berangkat') != '') {
            $departureDate = $request->input('tanggal-berangkat');
            $query->whereDate('departure_date', $departureDate);
        } else {
            $today = Carbon::now()->startOfDay();
            $tenDaysLater = Carbon::now()->addDays(10)->endOfDay();
            $query->whereBetween('departure_date', [$today, $tenDaysLater]);
        }
        $schedules = $query->distinct()->orderBy('departure_date')->orderBy('departure_time')->get();


        return view('homepage', compact('schedules', 'cities', 'ticketTypes', 'request'));
    }
}