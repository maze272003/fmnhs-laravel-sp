<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // database/seeders/DatabaseSeeder.php

public function run(): void
{
    $this->call([
        AdminSeeder::class,
        SectionSeeder::class,
        SubjectSeeder::class,    
        TeacherSeeder::class,
        StudentSeeder::class,
        RoomSeeder::class,
        ScheduleSeeder::class, 
        AssignmentSeeder::class,  
        GradeSeeder::class,      
        AttendanceSeeder::class,
        SchoolYearConfigSeeder::class,
        FeatureFlagSeeder::class,
    ]);
}
}