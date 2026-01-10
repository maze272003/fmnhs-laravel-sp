<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'name' => 'Administrator',
            'email' => 'admin@school.com',
            'password' => Hash::make('password'),
        ]);
        // sangbaanstefhaniemary@gmail.com
        Admin::updateOrCreate(
            ['email' => 'sangbaanstefhaniemary@gmail.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
            ]);
    }
}