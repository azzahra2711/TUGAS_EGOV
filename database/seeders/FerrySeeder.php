<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ferry;

class FerrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ferries = [
            ['name' => 'KM. Dharma Ferry 2'],
            ['name' => 'KM. Kirana I'],
            ['name' => 'KM. Dharma Kencana III'],
            ['name' => 'KM. Satya Kencana II'],
            ['name' => 'KM. Darma Rucitra 9'],
        ];

        foreach ($ferries as $ferry) {
            Ferry::create($ferry);
        }
    }
}

