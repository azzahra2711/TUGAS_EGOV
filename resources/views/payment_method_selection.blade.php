<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pembayaran</title>
  <link rel="stylesheet" href="css/Pembayaran.css">
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
                <button type="submit" style="background:none; border:none; color:inherit; cursor:pointer; padding:0; font:inherit;">
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
        <p>Virtual Account</p>
        <form id="formPembayaran" action="{{ route('process.payment') }}" method="POST">
            @csrf
                <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                <input type="hidden" name="quantities" value="{{ $quantities }}">
                <input type="hidden" name="ticket_type" value="{{ $ticketType }}">
                <input type="hidden" name="selected_seat_numbers" value="{{ json_encode($selectedSeatNumbers) }}">
                {{-- Jika ada details tambahan seperti VIP room, perlu dikirim juga --}}
                @if ($ticketType === 'Kamar VIP')
                    {{-- The vip_room_details inputs are part of this form via the 'form' attribute --}}
                @endif
                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

            <div class="va-list">
                <label class="va-item">
                    <input type="radio" name="payment_method" value="bni" required />
                    <span>BNI VA</span>
                    <img src="{{ asset('images/bni.png') }}" alt="BNI">
                </label>

                <label class="va-item">
                    <input type="radio" name="payment_method" value="bri" />
                    <span>BRI VA</span>
                    <img src="{{ asset('images/BRI.svg') }}" alt="BRI VA">
                </label>

                <label class="va-item">
                    <input type="radio" name="payment_method" value="mandiri" />
                    <span>MANDIRI VA</span>
                    <img src="{{ asset('images/Mandiri.webp') }}" alt="Mandiri">
                </label>

                <label class="va-item">
                    <input type="radio" name="payment_method" value="bca" />
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
      const form = document.getElementById('formPembayaran');
  
      lanjutBtn.disabled = true;
  
      metodeRadios.forEach(radio => {
        radio.addEventListener('change', function () {
          lanjutBtn.disabled = false;
        });
      });
    });
  </script>  
</body>
</html>