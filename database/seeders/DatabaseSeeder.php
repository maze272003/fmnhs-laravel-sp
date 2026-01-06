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
        TeacherSeeder::class,    // Siguraduhing may file ka nito
        SubjectSeeder::class,    
        SectionSeeder::class,    
        StudentSeeder::class,    
        ScheduleSeeder::class,   
        GradeSeeder::class,      
        AttendanceSeeder::class, 
    ]);
}
}