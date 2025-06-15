<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ticket;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tickets = [
            ['name' => 'Penumpang'],
            ['name' => 'Kendaraan'],
            ['name' => 'Kamar VIP'],
        ];

        foreach ($tickets as $ticket) {
            Ticket::create($ticket);
        }
    }
}

