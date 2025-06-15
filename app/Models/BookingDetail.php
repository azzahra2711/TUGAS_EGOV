<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'seat_id',
        'seat_type_id',
        'quantity',
        'price_per_unit',
    ];

    // Relasi ke Booking
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // Relasi ke Seat
    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }

    // Relasi ke SeatType
    public function seatType()
    {
        return $this->belongsTo(SeatType::class);
    }
}
