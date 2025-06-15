<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import facade Auth
use Illuminate\Validation\ValidationException; // Untuk menangani error validasi

class LoginController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function create()
    {
        return view('login'); // Mengarahkan ke resources/views/login.blade.php
    }

    /**
     * Menangani percobaan login pengguna.
     */
    public function authenticate(Request $request)
    {
        // Validasi input form login
        $credentials = $request->validate([
            // Laravel Auth secara default menggunakan 'email' dan 'password'
            // Jika Anda menggunakan 'username' di form, Anda bisa ubah di sini
            'username' => ['required', 'string'], // Asumsi Anda menggunakan 'username' atau 'email'
            'password' => ['required'],
        ]);

        // Coba untuk otentikasi menggunakan 'email' atau 'username' sebagai identifikasi
        // Pertama, coba dengan 'email'
        if (Auth::attempt(['email' => $credentials['username'], 'password' => $credentials['password']])) {
            // Regenerasi session untuk mencegah session fixation attacks
            $request->session()->regenerate();
            // Redirect ke homepage setelah login berhasil
            return redirect()->intended('/homepage')->with('success', 'Selamat datang kembali!');
        }

        // Jika otentikasi gagal dengan email, coba dengan 'name' (username)
        // Ini jika field 'username' di form Anda sebenarnya adalah kolom 'name' di tabel users
        if (Auth::attempt(['name' => $credentials['username'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return redirect()->intended('/homepage')->with('success', 'Selamat datang kembali!');
        }

        // Jika otentikasi gagal (baik dengan email maupun username/name)
        // Melemparkan error validasi kembali ke form login
        throw ValidationException::withMessages([
            'username' => __('Kredensial yang diberikan tidak cocok dengan catatan kami.'),
        ]);
    }

    /**
     * Log out pengguna.
     */
    public function logout(Request $request)
    {
        Auth::logout(); // Logout pengguna

        // Invalidasi sesi saat ini
        $request->session()->invalidate();
        // Regenerasi token CSRF
        $request->session()->regenerateToken();

        // Redirect ke halaman login dengan pesan sukses
        return redirect('/login')->with('success', 'Anda telah berhasil logout.');
    }
}

