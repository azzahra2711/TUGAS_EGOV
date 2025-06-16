<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Pemesanan Tiket Kapal Ferry</title>
    <link rel="stylesheet" href="{{ asset('css/Pembayaran.css') }}">
    <style>
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

            <form action="{{ route('process.payment') }}" method="POST" id="formPembayaran">
                @csrf

                {{-- Kirim hanya payment method & token --}}
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

                <div style="display: flex; justify-content: center;">
                    <button type="submit" class="btn-lanjut" id="lanjutBtn">Lanjutkan</button>
                </div>

            </form>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const metodeRadios = document.querySelectorAll('input[name="payment_method"]');
            const lanjutBtn = document.getElementById('lanjutBtn');

            metodeRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    lanjutBtn.classList.add('active');
                });
            });

            document.getElementById('formPembayaran').addEventListener('submit', function (e) {
                if (!document.querySelector('input[name="payment_method"]:checked')) {
                    e.preventDefault();
                    alert('Silakan pilih metode pembayaran terlebih dahulu.');
                }
            });
        });
    </script>
</body>

</html>