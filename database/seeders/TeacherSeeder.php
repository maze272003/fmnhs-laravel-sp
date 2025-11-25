<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SPECIFIC TEACHER (Para may sure login ka)
        // Check muna kung nag-eexist para iwas duplicate error kapag nag-seed ulit
        $exists = Teacher::where('email', 'teacher@gmail.com')->exists();
        
        if (!$exists) {
            Teacher::create([
                'employee_id' => 'T-2025-001',
                'first_name'  => 'Juan',
                'last_name'   => 'Dela Cruz',
                'email'       => 'teacher@gmail.com',
                'password'    => Hash::make('password'),
                'department'  => 'Science',
            ]);
        }

        // 2. RANDOM TEACHERS (Generate 20 more)
        Teacher::factory(20)->create();
    }
}