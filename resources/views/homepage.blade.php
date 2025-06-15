<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Pemesanan Tiket Kapal Ferry</title>
    <link rel="stylesheet" href="{{ asset('css/Homepage.css') }}">
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

    <main class="container">
        <h2><b>Pencarian Jadwal</b></h2>
        <div class="form-box">
            <form action="{{ route('homepage') }}" method="GET" id="filterForm">
                <select name="jenis-tiket" required>
                    <option disabled {{ !request('jenis-tiket') ? 'selected' : '' }}>Pilih Jenis Tiket</option>
                    @foreach ($ticketTypes as $ticketType)
                        <option value="{{ $ticketType->name }}" {{ request('jenis-tiket') == $ticketType->name ? 'selected' : '' }}>
                            {{ $ticketType->name }}
                        </option>
                    @endforeach
                </select>

                <select name="kota-asal" required>
                    <option disabled {{ !request('kota-asal') ? 'selected' : '' }}>Pilih Kota Asal</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->name }}/{{ $city->code }}" {{ request('kota-asal') == $city->name . '/' . $city->code ? 'selected' : '' }}>
                            {{ $city->name }} ({{ $city->code }})
                        </option>
                    @endforeach
                </select>

                <select name="kota-tujuan" required>
                    <option disabled {{ !request('kota-tujuan') ? 'selected' : '' }}>Pilih Kota Tujuan</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->name }}/{{ $city->code }}" {{ request('kota-tujuan') == $city->name . '/' . $city->code ? 'selected' : '' }}>
                            {{ $city->name }} ({{ $city->code }})
                        </option>
                    @endforeach
                </select>

                <label for="tanggal-berangkat">Tanggal Berangkat</label>
                <input type="date" id="tanggal-berangkat" name="tanggal-berangkat"
                    value="{{ request('tanggal-berangkat') }}" required onclick="this.showPicker()">

                <button type="submit" class="cek-btn">🔍 Cek</button>
            </form>
        </div>

        @if ($schedules->isNotEmpty() || $request->hasAny(['jenis-tiket', 'kota-asal', 'kota-tujuan', 'tanggal-berangkat']))
            <div class="jadwal-box" id="jadwalBox" style="display: block;">
                <h2><b>Jadwal Keberangkatan</b></h2>
                @if ($schedules->isEmpty())
                    <p>Tidak ada jadwal yang tersedia untuk kriteria pencarian Anda.</p>
                @else
                    <table>
                        <thead>
                            <tr>
                                <th>Rute</th>
                                <th>Nama Kapal</th>
                                <th>Waktu Berangkat</th>
                                <th>Waktu Tiba</th>
                                <th>Tipe Tiket</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($schedules as $schedule)
                                @php
                                    $filteredFares = $schedule->fares->filter(function ($fare) use ($request) {
                                        $selectedTicketTypeName = $request->input('jenis-tiket');
                                        if (!$selectedTicketTypeName || !$fare->seatType) {
                                            return true; // If no type selected or seatType is null, include all for now (shouldn't happen with proper DB)
                                        }
                                        if ($selectedTicketTypeName === 'Penumpang') {
                                            // Ensure 'Dewasa' and 'Anak-anak' are included for 'Penumpang'
                                            return in_array($fare->seatType->name, ['Dewasa', 'Anak-anak']);
                                        } elseif ($selectedTicketTypeName === 'Kendaraan') {
                                            return Str::contains($fare->seatType->name, 'Kendaraan');
                                        } elseif ($selectedTicketTypeName === 'Kamar VIP') {
                                            return Str::contains(Str::lower($fare->seatType->name), 'vip');
                                        }
                                        return false;
                                    });
                                @endphp

                                {{-- Only display the schedule row if there are filtered fares for it --}}
                                @if ($filteredFares->isNotEmpty())
                                    @foreach ($filteredFares as $fare)
                                        <tr data-schedule-id="{{ $schedule->id }}">
                                            <td>{{ $schedule->originCity->code }} - {{ $schedule->destinationCity->code }}
                                            </td>
                                            <td>{{ $schedule->ferry->name ?? 'N/A' }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($schedule->departure_date)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}<br>
                                                {{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }} WIB
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($schedule->arrival_date)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}<br>
                                                {{ \Carbon\Carbon::parse($schedule->arrival_time)->format('H:i') }} WIB
                                            </td>
                                            <td>{{ $fare->seatType->name ?? 'N/A' }}</td>
                                            <td>Rp. {{ number_format($fare->price, 0, ',', '.') }}</td>
                                            <td>
                                                <button type="button" class="pesan-btn" data-schedule-id="{{ $schedule->id }}"
                                                    data-fare-id="{{ $fare->id }}"
                                                    data-ticket-type-name="{{ $request->input('jenis-tiket') }}"
                                                    data-seat-type-name="{{ $fare->seatType->name ?? 'N/A' }}">
                                                    Pesan
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @else
            <div class="jadwal-box" id="jadwalBox" style="display: none;">
                <p>Silakan isi detail pencarian Anda untuk melihat jadwal keberangkatan.</p>
            </div>
        @endif
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterForm = document.getElementById('filterForm');
            const formBox = document.querySelector('.form-box');
            const cekBtn = document.querySelector('.cek-btn');
            const pesanButtons = document.querySelectorAll('.pesan-btn');
            const jenisTiketDropdown = document.querySelector('select[name="jenis-tiket"]');

            const removeErrorMessage = () => {
                const existingError = formBox.querySelector('.error-message');
                if (existingError) {
                    existingError.remove();
                }
            };

            cekBtn.addEventListener('click', function (e) {
                removeErrorMessage();
                const kotaAsal = document.querySelector('select[name="kota-asal"]');
                const kotaTujuan = document.querySelector('select[name="kota-tujuan"]');
                const tanggal = document.getElementById('tanggal-berangkat');

                // Check if any required fields are not selected/filled (index 0 usually means "Pilih...")
                if (jenisTiketDropdown.selectedIndex === 0 || kotaAsal.selectedIndex === 0 || kotaTujuan
                    .selectedIndex === 0 || tanggal.value === '') {
                    e.preventDefault(); // Prevent form submission
                    const errorMessage = document.createElement('div');
                    errorMessage.textContent = 'Silakan lengkapi semua data pencarian terlebih dahulu.';
                    errorMessage.style.color = 'red';
                    errorMessage.style.marginBottom = '10px';
                    errorMessage.classList.add('error-message');
                    formBox.insertBefore(errorMessage, formBox.firstChild);
                }
            });

            pesanButtons.forEach(button => {
                button.addEventListener('click', function () {
                    removeErrorMessage();

                    const scheduleId = this.dataset.scheduleId;
                    const fareId = this.dataset.fareId;
                    const selectedMainJenisTiket = this.dataset.ticketTypeName;
                    const seatTypeName = this.dataset.seatTypeName;

                    let quantities = {};
                    let totalTickets = 0;

                    // When "Jumlah" column is removed, we assume 1 ticket for the selected fare type.
                    quantities[fareId] = 1;
                    totalTickets = 1;

                    // Error message if somehow totalTickets is 0 (shouldn't happen with current logic)
                    if (totalTickets === 0) {
                        const errorMessage = document.createElement('div');
                        errorMessage.textContent = `Silakan pilih setidaknya 1 tiket ${selectedMainJenisTiket}.`;
                        errorMessage.style.color = 'red';
                        errorMessage.style.marginBottom = '10px';
                        errorMessage.classList.add('error-message');
                        formBox.insertBefore(errorMessage, formBox.firstChild);
                        return;
                    }

                    if (selectedMainJenisTiket === 'Penumpang') {
                        const queryString = new URLSearchParams({
                            schedule_id: scheduleId,
                            quantities: JSON.stringify(quantities)
                        }).toString();
                        window.location.href = `{{ route('select.seats') }}?${queryString}`;
                    } else {
                        // For 'Kendaraan' and 'Kamar VIP', redirect to detail-pemesanan
                        const queryString = new URLSearchParams({
                            schedule_id: scheduleId,
                            quantities: JSON.stringify(quantities),
                            ticket_type: selectedMainJenisTiket // Pass the main ticket type
                        }).toString();
                        window.location.href = `{{ route('order.detail') }}?${queryString}`;
                    }
                });
            });
        });
    </script>
</body>

</html>