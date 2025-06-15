<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Contoh user admin
        User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // password
            'phone_number' => '081234567890',
            'nik' => '1234567890123456',
            'address' => 'Jl. Admin No. 1',
            'city' => 'Jakarta',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        // Beberapa user dummy lainnya
        User::factory()->count(10)->create();
    }
}

