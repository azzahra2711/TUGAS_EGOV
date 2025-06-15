<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Kursi - Pemesanan Tiket Kapal Ferry</title>
    <link rel="stylesheet" href="{{ asset('css/Pilihkursi.css') }}"> {{-- Menggunakan CSS terpisah --}}
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

    <main class="seat-layout">
        @if (session('error'))
            <div style="color: red; margin-bottom: 15px; text-align: center;">
                {{ session('error') }}
            </div>
        @endif

        <div class="detail">
            <h3>DETAIL PERJALANAN</h3>
            <p><b>{{ $schedule->originCity->name }} ({{ $schedule->originCity->code }}) &rarr;
                    {{ $schedule->destinationCity->name }} ({{ $schedule->destinationCity->code }})</b></p>
            <p>{{ \Carbon\Carbon::parse($schedule->departure_date)->locale('id')->isoFormat('dddd, D MMMM BCE') }}</p>
            <p>Penumpang</p> {{-- Asumsi ini selalu untuk penumpang --}}
        </div>

        <form action="{{ route('order.detail') }}" method="POST" id="bookingForm">
            @csrf
            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
            <input type="hidden" name="ticket_type" value="Penumpang">
            <input type="hidden" name="quantities"
                value="{{ json_encode($selectedFares->pluck('selected_quantity', 'id')) }}">
            <input type="hidden" name="selected_seat_numbers" id="selectedSeatNumbersInput"> {{-- Untuk menyimpan nomor
            kursi terpilih --}}
            <input type="hidden" name="payment_method" value="Online Payment"> {{-- Default atau biarkan kosong jika ada
            pilihan nanti --}}
            <input type="hidden" name="payment_token" value="dummy_token_123"> {{-- Placeholder untuk token pembayaran
            --}}

            <div class="kursi-section">
                <h3>DATA KURSI</h3>
                <p>Silakan pilih <span id="requiredSeatsCount">{{ $totalTicketsNeeded }}</span> kursi.</p>
                <div class="grid-kursi seat-grid" id="seatGrid">
                    {{-- Kursi akan dirender di sini oleh JavaScript --}}
                </div>

                <div class="info-kursi">
                    <h4>INFORMASI KURSI</h4>
                    <div class="legend">
                        <div>
                            <div class="seat legend-seat selected"></div> Dipilih
                        </div>
                        <div>
                            <div class="seat legend-seat available"></div> Tersedia
                        </div>
                        <div>
                            <div class="seat legend-seat booked sold"></div> Terjual
                        </div>
                    </div>
                    <p><strong>Catatan:</strong></p>
                    <ul>
                        <li>Kursi sewaktu-waktu dapat dipesan oleh pengguna lain yang lebih dahulu menyelesaikan
                            pembelian.</li>
                        <li>Penumpang harap datang 15 menit sebelum keberangkatan untuk konfirmasi.</li>
                        <li><em style="color: blue">Jika anda terlambat maka tiket akan hangus dan tidak bisa
                                digunakan</em></li>
                    </ul>
                </div>
            </div>

            <div class="total-summary">
                Total Pembayaran: Rp. <span id="finalTotalAmount">
                    @php
                        $finalTotalAmount = 0;
                        foreach ($selectedFares as $fare) {
                            $finalTotalAmount += $fare->price * $fare->selected_quantity;
                        }
                        echo number_format($finalTotalAmount, 0, ',', '.');
                    @endphp
                </span>
            </div>

            <button type="submit" class="pesan-btn" id="pesanBtn">Pesan</button>
        </form>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const seatGrid = document.getElementById('seatGrid');
            const pesanBtn = document.getElementById('pesanBtn');
            const requiredSeatsCountSpan = document.getElementById('requiredSeatsCount');
            const selectedSeatNumbersInput = document.getElementById('selectedSeatNumbersInput');

            const totalSeatsToSelect = parseInt(requiredSeatsCountSpan.textContent);
            let selectedSeats = [];

            // Menggunakan variabel Blade yang dilewatkan dari controller
            const ferryTotalSeats = {{ $ferryTotalSeats }}; // Dari BookingController
            const bookedSeatsArray = @json($unavailableSeatNumbers); // Dari BookingController, ini adalah kursi yang is_available = 0

            // Render kursi secara dinamis
            function renderSeats() {
                seatGrid.innerHTML = ''; // Bersihkan grid
                for (let i = 1; i <= ferryTotalSeats; i++) {
                    const seatButton = document.createElement('button');
                    seatButton.classList.add('seat');
                    seatButton.textContent = i;
                    seatButton.dataset.seatNumber = i;

                    if (bookedSeatsArray.includes(i)) { // Memeriksa jika nomor kursi ada di daftar yang tidak tersedia
                        seatButton.classList.add('booked');
                        seatButton.setAttribute('disabled', 'true'); // Nonaktifkan kursi terjual
                    } else {
                        seatButton.classList.add('available');
                    }

                    if (selectedSeats.includes(i)) {
                        seatButton.classList.add('selected');
                    }

                    seatGrid.appendChild(seatButton);
                }
            }

            // Handler klik kursi
            seatGrid.addEventListener('click', (event) => {
                const clickedSeat = event.target.closest('.seat');
                if (!clickedSeat || clickedSeat.classList.contains('booked')) {
                    return; // Abaikan klik jika bukan kursi atau kursi sudah terjual
                }

                const seatNumber = parseInt(clickedSeat.dataset.seatNumber);

                if (clickedSeat.classList.contains('selected')) {
                    // Batalkan pemilihan
                    clickedSeat.classList.remove('selected');
                    selectedSeats = selectedSeats.filter(s => s !== seatNumber);
                } else {
                    // Pilih kursi
                    if (selectedSeats.length < totalSeatsToSelect) {
                        clickedSeat.classList.add('selected');
                        selectedSeats.push(seatNumber);
                        selectedSeats.sort((a, b) => a - b); // Urutkan nomor kursi
                    } else {
                        alert(`Anda hanya bisa memilih ${totalSeatsToSelect} kursi.`);
                    }
                }

                // Update input tersembunyi
                selectedSeatNumbersInput.value = JSON.stringify(selectedSeats);

                // Aktifkan/nonaktifkan tombol pesan
                if (selectedSeats.length === totalSeatsToSelect) {
                    pesanBtn.style.pointerEvents = 'auto';
                    pesanBtn.style.opacity = '1';
                } else {
                    pesanBtn.style.pointerEvents = 'none';
                    pesanBtn.style.opacity = '0.5';
                }
            });

            // Inisialisasi tampilan
            renderSeats();

            // Nonaktifkan tombol pesan di awal
            pesanBtn.style.pointerEvents = 'none';
            pesanBtn.style.opacity = '0.5';
        });
    </script>
</body>

</html>