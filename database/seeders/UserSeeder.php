<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'email' => "dvelezflores28@gmail.com",
            'verifique_email' => true,
            'password' => Hash::make("admin2024"),
            'active' => true,
            'root'=> true,
        ]);
    }
}
