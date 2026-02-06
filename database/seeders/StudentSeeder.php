<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Section;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // Get the ID for "Rizal" section
        $rizalSection = Section::where('name', 'Rizal')->first();
        $aguinaldoSection = Section::where('name', 'Aguinaldo')->first();

        // 1. CREATE SPECIFIC STUDENTS
        Student::create([
            'lrn' => '100000000001',
            'first_name' => 'Developer',
            'last_name' => 'Developers',
            'email' => 'dev@gmail.com',
            'password' => Hash::make('password'),
            'section_id' => $rizalSection->id,
            'enrollment_type' => 'Regular',
            'school_year' => '2024-2025',
        ]);

        Student::create([
            'lrn' => '100000000002',
            'first_name' => 'Elice',
            'last_name' => 'Erman',
            'email' => 'elicegerman@gmail.com',
            'password' => Hash::make('password'),
            'section_id' => $rizalSection->id,
            'enrollment_type' => 'Regular',
            'school_year' => '2024-2025',
        ]);

        // Grade 7 new enrollee
        if ($aguinaldoSection) {
            Student::create([
                'lrn' => '100000000003',
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'juandelacruz@gmail.com',
                'password' => Hash::make('password'),
                'section_id' => $aguinaldoSection->id,
                'enrollment_type' => 'Regular',
                'school_year' => '2024-2025',
            ]);

            // Transferee example
            Student::create([
                'lrn' => '100000000005',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'email' => 'mariasantos@gmail.com',
                'password' => Hash::make('password'),
                'section_id' => $aguinaldoSection->id,
                'enrollment_type' => 'Transferee',
                'school_year' => '2024-2025',
            ]);
        }

        // 2. CREATE RANDOM STUDENTS
        Student::factory(50)->create([
            'password' => Hash::make('password'),
        ]);
    }
}