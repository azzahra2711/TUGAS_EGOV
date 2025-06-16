<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import facade Auth
use Illuminate\Validation\ValidationException; // Untuk menangani error validasi

class LoginController extends Controller
{
    public function create()
    {
        return view('login'); 
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'], 
            'password' => ['required'],
        ]);

        if (Auth::attempt(['email' => $credentials['username'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return redirect()->intended('/homepage')->with('success', 'Selamat datang kembali!');
        }

        if (Auth::attempt(['name' => $credentials['username'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return redirect()->intended('/homepage')->with('success', 'Selamat datang kembali!');
        }

        throw ValidationException::withMessages([
            'username' => __('Kredensial yang diberikan tidak cocok dengan catatan kami.'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout(); 

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('success', 'Anda telah berhasil logout.');
    }
}

