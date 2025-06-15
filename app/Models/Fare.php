<?php

// app/Models/Fare.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fare extends Model
{
    protected $fillable = [
        'schedule_id',
        'ticket_type_id', // Ini sebenarnya harusnya seat_type_id berdasarkan relasi di bawah
        'price',
        // tambahkan field lain jika ada
    ];

    // Relasi ke Ticket Type (seharusnya SeatType)
    // Perbaiki 'Ticket::class' menjadi 'SeatType::class' jika ini merujuk ke tabel seat_types
    public function ticketType()
    {
        return $this->belongsTo(Ticket::class, 'ticket_type_id'); // Pastikan ini benar, atau ubah ke SeatType jika sesuai
    }

    // Relasi ke Schedule (optional, jika belum ada)
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Get the seat type that owns the fare.
     */
    public function seatType()
    {
        return $this->belongsTo(SeatType::class);
    }
}