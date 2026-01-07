<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // database/seeders/DatabaseSeeder.php

public function run(): void
{
    $this->call([
        AdminSeeder::class,      // Gagawa tayo nito sa baba
        SectionSeeder::class,    // Siguraduhing may file ka nito
        SubjectSeeder::class,    
        TeacherSeeder::class,
        StudentSeeder::class,    
        ScheduleSeeder::class, 
        AssignmentSeeder::class,  
        GradeSeeder::class,      
        AttendanceSeeder::class, 
    ]);
}
}