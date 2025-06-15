<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Pastikan model User sudah ada dan benar
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session; // Import Session facade

class RegisterController extends Controller
{
    /**
     * Menampilkan halaman registrasi.
     */
    public function create()
    {
        return view('register');
    }

    /**
     * Menyimpan data registrasi user baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'nik' => 'nullable|string|max:255|unique:users,nik',
            'email' => 'required|email|unique:users,email',
            'alamat' => 'nullable|string|max:255',
            'kota' => 'nullable|string|max:255',
            'password' => 'required|string|min:6|confirmed', // 'confirmed' akan mencari password_confirmation
        ]);

        User::create([
            'name' => $validated['username'],
            'nik' => $validated['nik'],
            'email' => $validated['email'],
            'address' => $validated['alamat'],
            'city' => $validated['kota'],
            'password' => Hash::make($validated['password']),
        ]);

        // Menggunakan session flash untuk pesan sukses
        Session::flash('success', 'Registrasi berhasil. Silakan login!');
        return redirect('/login');
    }
}

