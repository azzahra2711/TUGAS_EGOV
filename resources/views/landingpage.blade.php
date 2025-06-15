<!-- resources/views/landingpage.blade.php -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ferry Indonesia</title>
    {{-- Memanggil CSS dari folder public/css --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <header class="main-header">
        <div class="logo">
            <img src="{{ asset('images/new-logo.png.png') }}" alt="Ferry Indonesia" />
        </div>
        <nav class="nav-menu">
            <ul>
                {{-- Menggunakan url() helper untuk link --}}
                <li><a href="{{ url('/') }}">Beranda</a></li>
                <li><a href="#">Info</a></li> {{-- Sesuaikan link ini jika ada halaman info --}}
                <li><a href="#">Jadwal</a></li> {{-- Sesuaikan link ini jika ada halaman jadwal --}}
                <li><a href="{{ url('login') }}">Login</a></li>
            </ul>
        </nav>
    </header>

    <div class="hero-image">
        <img src="{{ asset('images/Background.jpg') }}" alt="Kapal Ferry"
            style="width: 100%; height: 100vh; object-fit: cover; display: block;" />
    </div>

    <div class="container">
        <section>
            <h2>PT. DHARMA LAUTAN UTAMA</h2>
            <p><b>PT Dharma Lautan Utama</b> adalah sebuah perusahaan yang melayani transportasi laut dan penyeberangan
                feri di seluruh Indonesia.
                Segmen pasar kami terutama dari golongan menengah kebawah, selain itu juga membawa penumpang, kargo dan
                kendaraan.
                Hal itu menyebabkan muatan di segala lini harus sesuai dengan kemampuan pengguna.
                Kebijakan harga kami menunjukkan kepedulian dan komitmen untuk membantu masyarakat meningkatkan taraf
                kesejahteraan dan memajukan pembangunan ekonomi regional selaras dengan implementasi Otonomi Regional
                tahun 1999.</p>
        </section>

        <section>
            <h2>Layanan Kami</h2>
            <p>Kami melayani negeri dengan memberikan pelayanan terbaik bagi para pelanggan
                sebagai tamu kami yang terhormat.</p>
        </section>

        <section>
            <h2>Cara Memesan Tiket</h2>
            <p>Untuk memesan tiket, silakan login atau daftar akun terlebih dahulu.
                Setelah login, Anda dapat memilih rute, tanggal keberangkatan, dan jumlah penumpang.</p>
        </section>
    </div>

    {{-- Anda dapat menghapus atau mengaktifkan footer jika diperlukan --}}
    <footer>
        &copy; 2025 Ferry Indonesia. All rights reserved.
    </footer>
</body>

</html>