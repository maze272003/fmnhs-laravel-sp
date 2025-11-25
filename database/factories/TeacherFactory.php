<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class TeacherFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Gagawa ng ID na parang T-2025-1023
            'employee_id' => 'T-2025-' . fake()->unique()->numberBetween(1000, 9999),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'), // Default password para sa lahat
            'department' => fake()->randomElement([
                'Mathematics', 
                'Science', 
                'English', 
                'Filipino', 
                'Araling Panlipunan', 
                'TLE (Livelihood Education)', 
                'MAPEH', 
                'Values Education'
            ]),
        ];
    }
}