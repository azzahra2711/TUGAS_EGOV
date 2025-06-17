<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pemesanan & Pembayaran - Pemesanan Tiket Kapal Ferry</title>
    <link rel="stylesheet" href="{{ asset('css/KonfirmasiPembayaran.css') }}">
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

            @if ($ticketType === 'Dewasa')
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
                                <td>{{ $userName }}</td>
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
                @elseif ($ticketType === 'Kendaraan')
                <h3>DATA KENDARAAN</h3>
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Jenis Kendaraan</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($selectedFares as $index => $fare)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $fare->seatType->name }}</td>
                                <td>{{ $fare->selected_quantity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
        document.getElementById('paymentMethodForm').addEventListener('submit', function (e) {
    const continueBtn = document.getElementById('continueToPaymentBtn');
    if (continueBtn.disabled) {
        e.preventDefault(); // Cegah form terkirim secara tidak sengaja
    }
});

    </script>
</body>

</html>