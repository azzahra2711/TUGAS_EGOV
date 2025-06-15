<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'schedule_id',
        'booking_date',
        'total_amount',
        'status',
        // 'selected_seat_numbers_json', // Potentially add this column (TEXT or JSON type) to store selected seat numbers for 'Penumpang' bookings
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Schedule
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    // Relasi ke BookingDetail (jika ada) - Ini penting jika Anda ingin menyimpan detail per tiket/kursi
    public function bookingDetails()
    {
        return $this->hasMany(BookingDetail::class);
    }
}