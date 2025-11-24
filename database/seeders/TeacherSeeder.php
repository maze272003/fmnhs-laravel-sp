<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        Teacher::create([
            'employee_id' => 'T-2025-001',
            'first_name'  => 'Mr.',
            'last_name'   => 'Teacher',
            'email'       => 'teacher@gmail.com', // Test email
            'password'    => Hash::make('password'),
            'department'  => 'Science Dept',
        ]);
    }
}