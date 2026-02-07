<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::firstOrCreate(
            ['email' => 'admin@school.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );
        Admin::firstOrCreate(
            ['email' => 'sangbaanstefhaniemary@gmail.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );
    }
}