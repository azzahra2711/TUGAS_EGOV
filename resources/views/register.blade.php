<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registrasi - Ferry Indonesia</title>
    {{-- Memanggil CSS dari folder public/css --}}
    <link rel="stylesheet" href="{{ asset('css/login.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
</head>

<body>

    <header class="main-header">
        <div class="logo">
            <img src="{{ asset('images/new-logo.png.png') }}" alt="Ferry Indonesia" />
        </div>
        <nav class="nav-menu">
            <ul>
                <li><a href="{{ url('landingpage') }}">Beranda</a></li>
                <li><a href="#">Info</a></li> {{-- Sesuaikan link ini jika ada halaman info --}}
                <li><a href="#">Jadwal</a></li> {{-- Sesuaikan link ini jika ada halaman jadwal --}}
                <li><a href="{{ url('login') }}">Login</a></li>
            </ul>
        </nav>
    </header>

    <main class="login-section">
        <div class="login-container">
            <img src="{{ asset('images/Logo.png') }}" alt="Ferry Indonesia" />
            <form action="{{ route('register.store') }}" method="POST" id="signupForm">
                @csrf {{-- Token CSRF untuk keamanan Laravel --}}

                {{-- Menampilkan error validasi jika ada --}}
                @if ($errors->any())
                    <div class="alert alert-danger" style="color: red; margin-bottom: 10px;">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required />

                <label for="nik">NIK</label>
                <input type="text" id="nik" name="nik" value="{{ old('nik') }}" required />

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required />

                <label for="alamat">Alamat</label>
                <input type="text" id="alamat" name="alamat" value="{{ old('alamat') }}" required />

                <label for="kota">Kota/Kab</label>
                <input type="text" id="kota" name="kota" value="{{ old('kota') }}" required />

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required />

                <label for="password_confirmation">Konfirmasi Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required />

                <button type="submit">Sign Up</button>
            </form>
            <div class="login-options">
                <a href="#">Lupa Password?</a> {{-- Anda mungkin ingin membuat route untuk ini --}}
                <span>|</span>
                <a href="{{ url('login') }}">Sudah punya akun? Login</a> {{-- Ubah ini agar mengarah ke login --}}
            </div>
        </div>
    </main>

    <script>
        // Skrip JavaScript ini akan dihapus karena validasi dan redirect ditangani oleh Laravel
        // Namun, jika Anda ingin menggunakan JavaScript untuk feedback UI (tanpa alert), bisa disesuaikan
        // Contoh: menampilkan modal kustom, bukan alert.
    </script>
</body>

</html>