<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pemesanan & Pembayaran - Pemesanan Tiket Kapal Ferry</title>
    <link rel="stylesheet" href="{{ asset('css/Homepage.css') }}">
    <style>
        /* Styles adapted from Konfirmasipembayaran.css or common styles */
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

        .card h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
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

        .checkbox {
            display: flex;
            align-items: center;
            margin-top: 20px;
            font-size: 0.95em;
        }

        .checkbox input[type="checkbox"] {
            margin-right: 10px;
            transform: scale(1.2);
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

        .error-message {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }

        .vehicle-details-form,
        .vip-room-details-form {
            margin-top: 15px;
            padding: 10px;
            border: 1px dashed #ccc;
            border-radius: 5px;
            background-color: #fcfcfc;
        }

        .vehicle-details-form h5,
        .vip-room-details-form h5 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #444;
        }

        .vehicle-details-form label,
        .vip-room-details-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: normal;
        }

        .vehicle-details-form input,
        .vehicle-details-form select,
        .vip-room-details-form input {
            width: calc(100% - 20px);
            /* Adjust for padding */
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <header class="topbar">
        <div class="left">
            <img src="{{ asset('images/new-logo.png.png') }}" alt="Logo Ferry" class="logo">
        </div>
        <div class="right">
            <span class="user">👤 {{ Auth::user()->name ?? 'Pengguna' }} </span>
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
            <h2>DETAIL PESANAN</h2>

            @if (session('error'))
                <div class="error-message">
                    {{ session('error') }}
                </div>
            @endif

            <p><strong>{{ $schedule->originCity->name }} ({{ $schedule->originCity->code }}) &rarr;
                    {{ $schedule->destinationCity->name }} ({{ $schedule->destinationCity->code }})</strong></p>
            <p>{{ \Carbon\Carbon::parse($schedule->departure_date)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                Pukul {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }} WIB</p>
            <hr>

            <h3>DATA PEMESAN</h3>
            <div class="pemesan-info">
                <div class="info-left">
                    <p><strong>Nama Pemesan</strong><br>👤 {{ $userName }}</p>
                </div>
                <div class="info-right">
                    <p>📞 0821xxxx <br>✉️ {{ $userEmail }}</p>
                </div>
            </div>

            <hr>

            @if ($ticketType === 'Penumpang')
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
                        @forelse ($selectedSeatNumbers as $index => $seatNumber)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                {{-- Anda perlu mengimplementasikan cara untuk mendapatkan nama penumpang di sini.
                                Contohnya, jika Anda memiliki kolom 'passenger_name' di tabel booking_details
                                atau menyimpannya di sessions/JSON pada Booking model.
                                Untuk sementara, ini adalah placeholder. --}}
                                <td>Penumpang {{ $index + 1 }}</td>
                                <td>{{ $seatNumber }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">Tidak ada penumpang atau kursi yang dipilih.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <hr>
            @elseif ($ticketType === 'Kamar VIP')
                <h3>DETAIL KAMAR VIP</h3>
                @foreach ($selectedFares as $fare)
                    <p>{{ $fare->selected_quantity }} x {{ $fare->seatType->name }}</p>
                    <div class="vip-room-details-form">
                        <h5>Detail Kamar VIP {{ $loop->iteration }}</h5>
                        {{-- Formulir ini akan dikirimkan ke showPaymentMethods --}}
                        <label for="vip_preference_{{ $loop->index }}">Preferensi Kamar (opsional):</label>
                        <input type="text" id="vip_preference_{{ $loop->index }}"
                            name="vip_room_details[{{ $loop->index }}][preference]" form="paymentMethodForm"> {{-- Link to the
                        form below --}}
                    </div>
                @endforeach
                <hr>
            @endif

            <div class="harga">
                <p>Harga Tiket
                    <span>Rp. {{ number_format($totalAmount, 0, ',', '.') }}</span>
                </p>
                <p>Jumlah Tiket
                    <span>X {{ $selectedFares->sum('selected_quantity') }}</span>
                </p>
                <p class="total">Total Harga
                    <span>Rp. {{ number_format($totalAmount, 0, ',', '.') }}</span>
                </p>
            </div>

            <label class="checkbox">
                <input type="checkbox" id="termsCheckbox" checked />
                Saya telah membaca Syarat & Ketentuan
            </label>

            {{-- Form untuk melanjutkan ke pemilihan metode pembayaran --}}
            <form action="{{ route('show.payment.methods') }}" method="POST" id="paymentMethodForm">
                @csrf
                <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                <input type="hidden" name="quantities"
                    value="{{ json_encode($selectedFares->pluck('selected_quantity', 'id')) }}">
                <input type="hidden" name="ticket_type" value="{{ $ticketType }}">
                <input type="hidden" name="selected_seat_numbers" value="{{ json_encode($selectedSeatNumbers) }}">
                {{-- Jika ada details tambahan seperti VIP room, perlu dikirim juga --}}
                @if ($ticketType === 'Kamar VIP')
                    {{-- The vip_room_details inputs are part of this form via the 'form' attribute --}}
                @endif
                <button type="submit" class="btn-lanjut" id="continueToPaymentBtn" disabled>Lanjutkan
                    Pembayaran</button>
            </form>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const termsCheckbox = document.getElementById('termsCheckbox');
            const continueToPaymentBtn = document.getElementById('continueToPaymentBtn');

            // Initial check
            continueToPaymentBtn.disabled = !termsCheckbox.checked;

            termsCheckbox.addEventListener('change', function () {
                continueToPaymentBtn.disabled = !this.checked;
            });
        });
    </script>
</body>

</html>