<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController; 
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route untuk Landing Page
Route::get('/', function () {
    return view('landingpage');
});

// Routes untuk Autentikasi
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Routes untuk Registrasi
Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::middleware('auth')->group(function () {
    Route::get('/homepage', [HomepageController::class, 'index'])->name('homepage');
    
    // Route for 'Penumpang' (Passengers) - goes to seat selection
    Route::get('/pilihkursi', [BookingController::class, 'selectSeats'])->name('select.seats');

    // Route for 'Kendaraan' (Vehicles) and 'Kamar VIP' (VIP Rooms) - goes directly to order summary/payment
    // This now acts as the "Konfirmasi Pembayaran" page
    Route::post('/detail-pemesanan', [BookingController::class, 'showOrderDetail'])->name('order.detail');
    Route::post('/pilih-metode-pembayaran', [BookingController::class, 'showPaymentMethods'])->name('show.payment.methods');
    Route::post('/process-payment', [BookingController::class, 'processPayment'])->name('process.payment');
    Route::get('/booking-success/{booking_id}', [BookingController::class, 'bookingSuccess'])->name('booking.success');

});