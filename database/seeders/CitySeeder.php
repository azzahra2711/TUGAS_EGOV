<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            ['name' => 'Balikpapan (Pel.Semayang)', 'code' => 'BPN'],
            ['name' => 'Banjarmasin (Pel. Trisakti)', 'code' => 'BDJ'],
            ['name' => 'Batulicin (Pel. Samudra)', 'code' => 'BTW'],
            ['name' => 'Baubau (Pel. Murhun)', 'code' => 'BUW'],
            ['name' => 'Semarang (Pel. Tanjung Emas)', 'code' => 'SRG'],
            ['name' => 'Surabaya (Pel. Tanjung Perak)', 'code' => 'SBY'],
            ['name' => 'Makassar (Pel. Soekarno-Hatta)', 'code' => 'MKS'],
            ['name' => 'Kumai (Pel. Kumai)', 'code' => 'KUM'],
        ];

        foreach ($cities as $city) {
            City::create($city);
        }
    }
}

