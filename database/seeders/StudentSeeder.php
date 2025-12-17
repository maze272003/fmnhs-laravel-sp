<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use Illuminate\Support\Facades\Hash; // Importante para sa encryption

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CREATE SPECIFIC STUDENT (JM)
        Student::create([
            'lrn' => '100000000001', // Custom LRN
            'first_name' => 'Developer',
            'last_name' => 'Developers',
            'email' => 'dev@gmail.com',
            'password' => Hash::make('password'), // Encrypt ang password
            'grade_level' => 12,    // Example lang
            'strand' => 'STEM',     // Example lang
            'section' => 'Rizal',   // Example lang
        ]);

        Student::create([
            'lrn' => '100000000002', // Custom LRN
            'first_name' => 'Elice',
            'last_name' => 'Erman',
            'email' => 'elicegerman@gmail.com',
            'password' => Hash::make('password'), // Encrypt ang password
            'grade_level' => 12,    // Example lang
            'strand' => 'STEM',     // Example lang
            'section' => 'Rizal',   // Example lang
        ]);
        // 2. CREATE RANDOM STUDENTS (49 others)
        // Siguraduhin na ang Factory mo ay naglalagay din ng default password
        Student::factory(50)->create([
            'password' => Hash::make('password'), // Default pass ng random students
        ]);
    }
}