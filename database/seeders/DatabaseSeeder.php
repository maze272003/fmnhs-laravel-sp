<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            TeacherSeeder::class, // Create teachers first
            SectionSeeder::class, // Then create sections and assign teachers as advisors
            StudentSeeder::class, // Then create students and put them in sections
            SubjectSeeder::class,
            ScheduleSeeder::class, // Finally create schedules linking sections, subjects, and teachers
        ]);
    }
}