<?php

namespace Database\Factories;

use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lrn' => fake()->unique()->numerify('############'), 
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'section_id' => Section::inRandomOrder()->first()->id ?? 1,
            'enrollment_type' => fake()->randomElement(['Regular', 'Regular', 'Regular', 'Transferee']),
            'school_year' => '2024-2025',
        ];
    }
}