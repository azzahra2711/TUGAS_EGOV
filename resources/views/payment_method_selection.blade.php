<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Pemesanan Tiket Kapal Ferry</title>
    <link rel="stylesheet" href="{{ asset('css/Homepage.css') }}">
    <style>
        /* Styles adapted from Pembayaran.css or common styles */
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
            max-width: 500px;
            /* Adjust max-width as needed */
            box-sizing: border-box;
        }

        .card h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .card hr {
            border: none;
            border-top: 1px solid #eee;
            margin: 20px 0;
        }

        .card p {
            font-size: 1.1em;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .va-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .va-item {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .va-item:hover {
            background-color: #f9f9f9;
            border-color: #007bff;
        }

        .va-item input[type="radio"] {
            margin-right: 15px;
            transform: scale(1.2);
            /* Make radio button slightly larger */
        }

        .va-item span {
            flex-grow: 1;
            font-size: 1.1em;
            color: #333;
        }

        .va-item img {
            height: 30px;
            /* Adjust logo size */
            margin-left: 10px;
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
            opacity: 0.5;
            /* Disabled by default */
            pointer-events: none;
            /* Disabled by default */
        }

        .btn-lanjut:hover {
            background-color: #0056b3;
        }

        .btn-lanjut.active {
            opacity: 1;
            pointer-events: auto;
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
        <div class="card">
            <h2>PEMBAYARAN</h2>
            <hr>
            <p>Pilih Metode Pembayaran Virtual Account</p>

            {{-- Display total amount --}}
            <p style="text-align: center; font-size: 1.2em; color: #007bff;">
                Total yang harus dibayar: <strong>Rp. {{ number_format($totalAmount, 0, ',', '.') }}</strong>
            </p>
            <hr>

            <form action="{{ route('process.payment') }}" method="POST" id="formPembayaran">
                @csrf
                {{-- Hidden inputs to pass data to the final processPayment step --}}
                <input type="hidden" name="schedule_id" value="{{ $scheduleId }}">
                <input type="hidden" name="quantities" value="{{ $quantities }}">
                <input type="hidden" name="ticket_type" value="{{ $ticketType }}">
                <input type="hidden" name="selected_seat_numbers" value="{{ $selectedSeatNumbers }}">
                {{-- Pass VIP room details if applicable --}}
                @if ($ticketType === 'Kamar VIP' && !empty($vipRoomDetails))
                    @foreach ($vipRoomDetails as $index => $detail)
                        <input type="hidden" name="vip_room_details[{{ $index }}][preference]"
                            value="{{ $detail['preference'] ?? '' }}">
                    @endforeach
                @endif

                {{-- Payment token is a placeholder, in a real app this comes from a payment gateway --}}
                <input type="hidden" name="payment_token" value="dummy_payment_token_{{ uniqid() }}">

                <div class="va-list">
                    <label class="va-item">
                        <input type="radio" name="payment_method" value="Bank Transfer BNI" />
                        <span>BNI VA</span>
                        <img src="{{ asset('images/bni.png') }}" alt="BNI">
                    </label>

                    <label class="va-item">
                        <input type="radio" name="payment_method" value="Bank Transfer BRI" />
                        <span>BRI VA</span>
                        <img src="{{ asset('images/BRI.svg') }}" alt="BRI VA">
                    </label>

                    <label class="va-item">
                        <input type="radio" name="payment_method" value="Bank Transfer MANDIRI" />
                        <span>MANDIRI VA</span>
                        <img src="{{ asset('images/Mandiri.webp') }}" alt="Mandiri">
                    </label>

                    <label class="va-item">
                        <input type="radio" name="payment_method" value="Bank Transfer BCA" />
                        <span>BCA VA</span>
                        <img src="{{ asset('images/BCA.webp') }}" alt="BCA">
                    </label>
                </div>

                <button type="submit" class="btn-lanjut" id="lanjutBtn">Lanjutkan</button>
            </form>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const metodeRadios = document.querySelectorAll('input[name="payment_method"]');
            const lanjutBtn = document.getElementById('lanjutBtn');

            metodeRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    lanjutBtn.classList.add('active'); // Add 'active' class to enable button
                });
            });

            document.getElementById('formPembayaran').addEventListener('submit', function (e) {
                if (!document.querySelector('input[name="payment_method"]:checked')) {
                    e.preventDefault(); // Prevent form submission if no method is selected
                    alert('Silakan pilih metode pembayaran terlebih dahulu.');
                }
            });
        });
    </script>
</body>

</html>