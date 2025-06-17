<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan Berhasil!</title>
    <link rel="stylesheet" href="{{ asset('css/Struk.css') }}">
</head>

<body>
    <header class="topbar">
        <div class="left">
            <img src="{{ asset('images/new-logo.png.png') }}" alt="Logo Ferry" class="logo">
        </div>
        <div class="right">
            <span class="user">👤 {{ Auth::user()->name ?? 'Pengguna' }}</span>
            <span>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit"
                        style="background:none; border:none; color:inherit; cursor:pointer; padding:0; font:inherit;">
                        Logout / {{ Auth::user()->email ?? 'pengguna@example.com' }}
                    </button>
                </form>
            </span>
        </div>
    </header>
    <main>
        <section class="card">
            <div class="card-header">
                <h2>DETAIL PESANAN</h2>
                <img src="{{ asset('images/new-logo.png.png') }}" alt="Logo Ferry" class="logo-kanan">
            </div>

            <hr>

            <p><strong>{{ $booking->schedule->originCity->name }} ({{ $booking->schedule->originCity->code }}) &rarr;
                    {{ $booking->schedule->destinationCity->name }}
                    ({{ $booking->schedule->destinationCity->code }})</strong></p>
            <p>{{ \Carbon\Carbon::parse($booking->schedule->departure_date)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                Pukul {{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('H:i') }} WIB</p>

            <hr>

            <h3>DATA PEMESAN</h3>
            <div class="pemesan-info">
                <div class="info-left">
                    <p><strong>Nama Pemesan</strong><br>👤 {{ $booking->user->name ?? 'N/A' }}</p>
                </div>
                <div class="info-right">
                    <p>📞 0821xxxx <br>✉️ {{ $booking->user->email ?? 'N/A' }}</p>
                </div>
            </div>

            <hr>
            @if ($booking->selected_seat_numbers_json && count($booking->selected_seat_numbers_json) > 0)
    @php
        $data = $booking->selected_seat_numbers_json;
        $penumpang = collect($data)->where('type', 'Dewasa')->values();
        $vip = collect($data)->where('type', 'Kamar VIP')->values();
        $kendaraan = collect($data)->where('type', 'Kendaraan')->values();
    @endphp

    {{-- Penumpang --}}
    @php
    $penumpang = collect($booking->selected_seat_numbers_json)->filter(fn($item) => $item['type'] === 'Dewasa')->values();
@endphp

@if ($penumpang->count() > 0)
    <h3 class="text-lg font-bold mb-2">DATA PENUMPANG</h3>
    <table class="w-full border-collapse border border-gray-300 text-sm">
        <thead>
            <tr class="bg-gray-200">
                <th class="border p-2">No.</th>
                <th class="border p-2">Nama</th>
                <th class="border p-2">Kursi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($penumpang as $i => $item)
                <tr>
                    <td class="border p-2">{{ $i + 1 }}</td>
                    <td class="border p-2">{{ $booking->user->name ?? $item['name'] }}</td>
                    <td class="border p-2">{{ $item['seat_number'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif


    {{-- Kamar VIP --}}
    @if ($vip->count())
        <h3>DETAIL KAMAR VIP</h3>
        <table class="w-full border-collapse border border-gray-300 text-sm mb-4">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">No.</th>
                    <th class="border p-2">Tipe Kamar</th>
                    <th class="border p-2">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($vip as $i => $item)
                    <tr>
                        <td class="border p-2">{{ $i + 1 }}</td>
                        <td class="border p-2">{{ $item['seat_type'] }}</td>
                        <td class="border p-2">{{ $item['quantity'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Kendaraan --}}
    @if ($kendaraan->count())
        <h3>DATA KENDARAAN</h3>
        <table class="w-full border-collapse border border-gray-300 text-sm mb-4">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">No.</th>
                    <th class="border p-2">Jenis Kendaraan</th>
                    <th class="border p-2">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kendaraan as $i => $item)
                    <tr>
                        <td class="border p-2">{{ $i + 1 }}</td>
                        <td class="border p-2">{{ $item['seat_type'] }}</td>
                        <td class="border p-2">{{ $item['quantity'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endif



            {{-- @if ($bookedSeatNumbersForDisplay && count($bookedSeatNumbersForDisplay) > 0)
                <h3>DATA PENUMPANG</h3>
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama</th>
                            <th>Kursi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookedSeatNumbersForDisplay as $index => $seatNumber)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>Penumpang {{ $index + 1 }}</td>
                                <td>{{ $seatNumber }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <hr>
            @endif --}}

            {{-- If you have booking details with ticket types (e.g., VIP rooms), you'd loop them here --}}
            {{-- Example if using bookingDetails:
            @if ($booking->bookingDetails->isNotEmpty())
            <h3>DETAIL TIKET</h3>
            <ul>
                @foreach ($booking->bookingDetails as $item)
                <li>- {{ $item->quantity }} x {{ $item->fare->seatType->name ?? 'N/A' }} (Rp.
                    {{ number_format($item->price, 0, ',', '.') }} per tiket)
                    @if ($item->seat_number)
                    (Kursi: {{ $item->seat_number }})
                    @endif
                    @if ($item->vip_room_preference) // Example if you add this column
                    (Preferensi: {{ $item->vip_room_preference }})
                    @endif
                </li>
                @endforeach
            </ul>
            <hr>
            @endif
            --}}

            <div class="harga">
                <p>Harga Tiket <span>Rp. {{ number_format($booking->total_amount, 0, ',', '.') }}</span></p>
                {{-- This 'Jumlah Penumpang' or 'Jumlah Tiket' needs to be calculated based on selected fares --}}
                <p>Jumlah Tiket <span>X
                        {{ count($bookedSeatNumbersForDisplay) > 0 ? count($bookedSeatNumbersForDisplay) : 'N/A' }}</span>
                </p>
                <p class="total">Total Harga <span>Rp. {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                </p>
            </div>
            <a class="btn-lanjut" href="{{ route('homepage') }}">Kembali ke halaman utama</a>
        </section>
    </main>
</body>

</html>

