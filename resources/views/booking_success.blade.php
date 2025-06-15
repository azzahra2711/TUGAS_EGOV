<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan Berhasil!</title>
    <link rel="stylesheet" href="{{ asset('css/Homepage.css') }}">
    <style>
        /* Styles adapted from Konfirmasipembayaran.css (used by Struk.html) or common styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            background-color: #007bff;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topbar .logo {
            height: 40px;
        }

        .topbar .user {
            margin-right: 15px;
        }

        .topbar a {
            color: white;
            text-decoration: none;
        }

        main {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            /* Align to top for longer content */
            padding: 20px;
        }

        .card {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            /* Adjust max-width as needed */
            box-sizing: border-box;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-header h2 {
            text-align: left;
            /* Override card h2 centering */
            margin: 0;
            color: #333;
        }

        .card-header .logo-kanan {
            height: 50px;
            /* Adjust logo size */
        }

        .card hr {
            border: none;
            border-top: 1px solid #eee;
            margin: 20px 0;
        }

        .card h3 {
            color: #555;
            margin-top: 25px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        .card p {
            margin-bottom: 10px;
        }

        .pemesan-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .pemesan-info .info-left,
        .pemesan-info .info-right {
            flex: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .harga {
            text-align: right;
            margin-top: 20px;
            border-top: 1px dashed #ccc;
            padding-top: 15px;
        }

        .harga p {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 1.1em;
        }

        .harga .total {
            font-weight: bold;
            font-size: 1.3em;
            color: #007bff;
        }

        .btn-lanjut {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.1em;
            margin-top: 30px;
            transition: background-color 0.3s ease;
        }

        .btn-lanjut:hover {
            background-color: #0056b3;
        }
    </style>
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

            @if ($bookedSeatNumbersForDisplay && count($bookedSeatNumbersForDisplay) > 0)
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
                                {{-- Again, passenger name needs to come from your data storage --}}
                                <td>Penumpang {{ $index + 1 }}</td>
                                <td>{{ $seatNumber }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <hr>
            @endif

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