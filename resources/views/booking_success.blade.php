<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Kursi</title>
    <link rel="stylesheet" href="{{ asset('css/Konfirmasipembayaran.css') }}">
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
                <h2 style="text-align: center;">DETAIL PESANAN</h2>
                <img src="{{ asset('images/new-logo.png.png') }}" alt="Logo Ferry" class="logo-kanan">
            </div>

            <hr>

            <p><strong>{{ $booking->schedule->originCity->name }} ({{ $booking->schedule->originCity->code }}) ➝
                    {{ $booking->schedule->destinationCity->name }}
                    ({{ $booking->schedule->destinationCity->code }})</strong></p>
            <p>
                {{ \Carbon\Carbon::parse($booking->schedule->departure_date)->locale('id')->isoFormat('dddd, D MMMM YYYY') }},
                Pukul {{ \Carbon\Carbon::parse($booking->schedule->departure_time)->format('H:i') }} WIB
            </p>

            <hr>

            <h3>DATA PEMESAN</h3>
            <div class="pemesan-info">
                <div class="info-left">
                    <p><strong>Nama Pemesan</strong><br>👤 {{ $booking->user->name ?? 'N/A' }}</p>
                </div>
                <div class="info-right">
                    <p>📞 0821xxxx<br>✉️ {{ $booking->user->email ?? 'N/A' }}</p>
                </div>
            </div>

            <hr>

            @if (!empty($bookedSeatNumbersForDisplay))
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
            @endif

            <div class="harga">
                <p>Harga Tiket <span>Rp. {{ number_format($booking->total_amount, 0, ',', '.') }}</span></p>
                <p>Jumlah Tiket <span>X 1</span></p>
                <p class="total">Total Harga <span>Rp. {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
                </p>
            </div>

            <div style="display: flex; justify-content: center;">
                <a class="btn-lanjut" href="{{ route('homepage') }}">Kembali ke halaman utama</a>
            </div>
        </section>
    </main>
</body>

</html>