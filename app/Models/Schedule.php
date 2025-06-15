<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'ferry_id',
        'origin_city_id',
        'destination_city_id',
        'departure_date',
        'departure_time',
        'arrival_date',
        'arrival_time',
    ];

    // Relasi ke Ferry
    public function ferry()
    {
        return $this->belongsTo(Ferry::class);
    }

    // Relasi ke Kota Asal
    public function originCity()
    {
        return $this->belongsTo(City::class, 'origin_city_id');
    }

    // Relasi ke Kota Tujuan
    public function destinationCity()
    {
        return $this->belongsTo(City::class, 'destination_city_id');
    }


    public function fares()
    {
        return $this->hasMany(Fare::class);
    }

}
