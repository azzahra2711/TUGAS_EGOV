<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Ferry Indonesia</title>
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
                <li><a href="{{ url('/') }}">Beranda</a></li>
                <li><a href="#">Info</a></li> {{-- Sesuaikan link ini jika ada halaman info --}}
                <li><a href="#">Jadwal</a></li> {{-- Sesuaikan link ini jika ada halaman jadwal --}}
                <li><a href="{{ url('login') }}">Login</a></li>
            </ul>
        </nav>
    </header>

    <main class="login-section">
        <div class="login-container">
            <img src="{{ asset('images/Logo.png') }}" alt="Ferry Indonesia" />
            {{-- Form action diarahkan ke route login.post --}}
            <form action="{{ route('login.post') }}" method="post">
                @csrf {{-- Token CSRF untuk keamanan Laravel --}}

                {{-- Menampilkan pesan sukses dari registrasi atau logout --}}
                @if (Session::has('success'))
                    <div class="alert alert-success" style="color: green; margin-bottom: 10px;">
                        {{ Session::get('success') }}
                    </div>
                @endif

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

                <label for="username">Username/Email</label> {{-- Ubah label agar sesuai dengan input --}}
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus />

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required />

                <button type="submit">Login</button>
            </form>
            <div class="login-options">
                <a href="#">Lupa Password?</a> {{-- Anda mungkin ingin membuat route untuk ini --}}
                <span>|</span>
                <a href="{{ url('register') }}">Daftar Baru</a> {{-- Mengarahkan ke route register --}}
            </div>
        </div>
    </main>

    {{-- Skrip JavaScript ini dihapus karena validasi dan redirect ditangani oleh Laravel --}}
</body>

</html>