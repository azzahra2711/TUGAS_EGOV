<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController; 
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('landingpage');
});

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::middleware('auth')->group(function () {
    Route::get('/homepage', [HomepageController::class, 'index'])->name('homepage');
    
    Route::get('/pilihkursi', [BookingController::class, 'selectSeats'])->name('select.seats');
    Route::get('/detail-pemesanan', [BookingController::class, 'showOrderDetail'])->name('order.detail');
    Route::post('/pilih-metode-pembayaran', [BookingController::class, 'showPaymentMethods'])->name('show.payment.methods');
    Route::post('/proses-pembayaran', [BookingController::class, 'processPayment'])->name('process.payment');
    Route::get('/pemesanan-berhasil/{booking_id}', [BookingController::class, 'bookingSuccess'])->name('booking.success');
});