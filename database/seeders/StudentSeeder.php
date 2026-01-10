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

        // 1. CREATE SPECIFIC STUDENTS
        Student::create([
            'lrn' => '100000000001',
            'first_name' => 'Developer',
            'last_name' => 'Developers',
            'email' => 'dev@gmail.com',
            'password' => Hash::make('password'),
            'section_id' => $rizalSection->id,
        ]);

        Student::create([
            'lrn' => '100000000002',
            'first_name' => 'Elice',
            'last_name' => 'Erman',
            'email' => 'elicegerman@gmail.com',
            'password' => Hash::make('password'),
            'section_id' => $rizalSection->id,
        ]);
        // Student::create([
        //     'lrn' => '100000000004',
        //     'first_name' => 'Stefhanie',
        //     'last_name' => 'Sangbaan',
        //     'email' => 'sangbaanstefhaniemary@gmail.com',
        //     'password' => Hash::make('password'),
        //     'section_id' => $rizalSection->id,
        // ]);

        // 2. CREATE RANDOM STUDENTS
        Student::factory(50)->create([
            'password' => Hash::make('password'),
        ]);
    }
}