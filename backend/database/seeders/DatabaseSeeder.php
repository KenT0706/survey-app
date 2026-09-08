<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Creates one admin login so you don't have to use Tinker.
        // ⚠️ Change this password immediately after first login.
        User::firstOrCreate(
            ['email' => 'yslserene@gmail.com'],
            ['name' => 'Admin', 'password' => bcrypt('668866')]
        );
    }
}
