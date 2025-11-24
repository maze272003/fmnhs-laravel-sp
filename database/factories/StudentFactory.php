<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Random grade mula 7 hanggang 12
        $gradeLevel = fake()->numberBetween(7, 12);

        // Logic: Kung Senior High (11-12), lagyan ng Strand. Kung hindi, null.
        $strand = ($gradeLevel >= 11) 
            ? fake()->randomElement(['STEM', 'ABM', 'HUMSS', 'GAS', 'TVL-ICT', 'TVL-HE']) 
            : null;

        return [
            // Generate 12-digit LRN
            'lrn' => fake()->unique()->numerify('############'), 
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            
            'grade_level' => $gradeLevel,
            'strand' => $strand,
            
            // Random names of Filipino heroes or Saints for sections
            'section' => fake()->randomElement(['Rizal', 'Bonifacio', 'Mabini', 'Luna', 'Aguinaldo']),
        ];
    }
}
