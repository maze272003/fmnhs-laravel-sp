<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Independent / Config Data (MUST RUN FIRST)
            AdminSeeder::class,
            SchoolYearConfigSeeder::class, // <--- Moved UP here
            RoomSeeder::class,             // Good to create rooms early
            FeatureFlagSeeder::class,      // Good to create flags early

            // 2. Structure Data
            SectionSeeder::class,
            SubjectSeeder::class,    
            TeacherSeeder::class,

            // 3. Dependent Data (Needs Sections & School Years to exist first)
            StudentSeeder::class, 

            // 4. Transactional Data (Needs Students, Teachers, Subjects)
            ScheduleSeeder::class, 
            AssignmentSeeder::class,  
            GradeSeeder::class,      
            AttendanceSeeder::class,
        ]);
    }
}