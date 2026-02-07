<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Section;
use App\Models\SchoolYearConfig; // <--- Import this
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Load Sections
        $sections = Section::all()->keyBy('name');
        
        // 2. Load School Years (Key by the string "2025-2026" for easy lookup)
        $schoolYears = SchoolYearConfig::all()->keyBy('school_year');

        // Helper to safely get an ID, defaulting to the first one if not found
        $getSyId = fn($year) => $schoolYears[$year]->id ?? $schoolYears->first()->id;

        $studentData = [
            [
                'lrn' => '100000000001',
                'first_name' => 'Developer',
                'last_name' => 'Developers',
                'email' => 'dev@gmail.com',
                'section_name' => 'Rizal',
                'school_year' => '2025-2026',
            ],
            [
                'lrn' => '100000000002',
                'first_name' => 'Elice',
                'last_name' => 'Erman',
                'email' => 'elicegerman@gmail.com',
                'section_name' => 'Rizal',
                'school_year' => '2025-2026',
            ],
            [
                'lrn' => '100000000003',
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'email' => 'juandelacruz@gmail.com',
                'section_name' => 'Escudero',
                'school_year' => '2025-2026',
                'enrollment_type' => 'Regular',
            ],
            [
                'lrn' => '100000000005',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'email' => 'mariasantos@gmail.com',
                'section_name' => 'Escudero',
                'school_year' => '2025-2026',
                'enrollment_type' => 'Transferee',
            ],
        ];

        foreach ($studentData as $data) {
            if (!isset($sections[$data['section_name']])) continue;

            Student::firstOrCreate(
                ['lrn' => $data['lrn']],
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                    'section_id' => $sections[$data['section_name']]->id,
                    'enrollment_type' => $data['enrollment_type'] ?? 'Regular',
                    
                    // CHANGED: Use the ID from the lookup
                    'school_year_id' => $getSyId($data['school_year']),
                    
                    'is_alumni' => false,
                ]
            );
        }

        // Random Students Generation
        // We get all available SY IDs to pick from
        $syIds = $schoolYears->pluck('id')->toArray();
        $sectionsList = $sections->values(); // Reset keys for random picking

        for ($i = 0; $i < 20; $i++) {
            // Pick a random school year ID
            $randomSyId = $syIds[array_rand($syIds)];
            
            // Logic for alumni/grade level based on year is complex to simulate perfectly 
            // without knowing which SY ID corresponds to which year int.
            // Simplified: Randomly assign section, mostly Regulars.
            
            $randomSection = $sectionsList->random();

            Student::create([
                'lrn' => '10000000' . str_pad($i + 100, 4, '0', STR_PAD_LEFT),
                'first_name' => $this->getRandomFirstName(),
                'last_name' => $this->getRandomLastName(),
                'email' => 'student' . ($i + 100) . '@example.com',
                'password' => Hash::make('password'),
                'section_id' => $randomSection->id,
                'enrollment_type' => 'Regular',
                'school_year_id' => $randomSyId,
                'is_alumni' => false, // Defaulting to false for simplicity in seeder
            ]);
        }
    }

    private function getRandomFirstName()
    {
        $names = ['Jose', 'Ana', 'Miguel', 'Maria', 'Carlos', 'Sofia', 'Diego', 'Isabella', 'Andres', 'Carmen', 'Rafael', 'Lucia', 'Pedro', 'Elena', 'Javier', 'Victoria', 'Luis', 'Mariana', 'Antonio', 'Gabriela'];
        return $names[array_rand($names)];
    }

    private function getRandomLastName()
    {
        $names = ['Reyes', 'Cruz', 'Santos', 'Ramos', 'Garcia', 'Mendoza', 'Torres', 'Rivera', 'Flores', 'Castillo', 'Navarro', 'Vargas', 'Del Rosario', 'Castro', 'Aguilar', 'Fernandez', 'Lopez', 'Medina', 'Santiago', 'Ponce'];
        return $names[array_rand($names)];
    }
    public function getAvatarUrlAttribute()
    {
        // 1. If avatar is null or 'default.png', return a generated letter avatar
        if (empty($this->avatar) || $this->avatar === 'default.png') {
            $name = $this->first_name . ' ' . $this->last_name;
            return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random&color=fff&size=128';
        }

        // 2. If it's already a complete URL (e.g., from Google Login), return it as is
        if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }

        // 3. Return the S3 URL
        return Storage::disk('s3')->url($this->avatar);
    }
}