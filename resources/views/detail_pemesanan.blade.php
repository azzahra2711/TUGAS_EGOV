<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pemesanan & Pembayaran - Pemesanan Tiket Kapal Ferry</title>
    <link rel="stylesheet" href="{{ asset('css/konfirmasipembayaran.css') }}">
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
            <!-- Tambahan di atas tetap sama -->
            <h3>DATA PEMESAN</h3>
            <div class="pemesan-info">
                <div class="info-left">
                    <p><strong>Nama Pemesan</strong><br>👤 {{ $userName }}</p>
                </div>
                <div class="info-right">
                    <p>📞 0821xxxx<br>✉️ {{ $userEmail }}</p>
                </div>
            </div>

            <!-- Tambahkan tabel berikut -->
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama</th>
                        <th>Kode Kursi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($selectedSeatNumbersArray as $index => $seatNumber)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $userName }}</td>
                            <td>{{ $seatNumber }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Tetap lanjut ke bagian harga -->
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

            <form action="{{ route('show.payment.methods') }}" method="POST" id="paymentMethodForm">
                @csrf
                <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                <input type="hidden" name="quantities"
                    value="{{ json_encode($selectedFares->pluck('selected_quantity', 'id')) }}">
                <input type="hidden" name="selected_seat_numbers" value="{{ json_encode($selectedSeatNumbersArray) }}">
                <div style="display: flex; justify-content: center;">
                    <button type="submit" class="btn-lanjut" id="continueToPaymentBtn" disabled>Lanjutkan
                        Pembayaran</button>
                </div>
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