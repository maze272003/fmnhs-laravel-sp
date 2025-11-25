<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            // --- JUNIOR HIGH SCHOOL (GRADES 7-10) CORE SUBJECTS ---
            // Grade 7
            ['code' => 'ENG-7', 'name' => 'English 7 (Philippine Literature)', 'description' => 'JHS Core Subject'],
            ['code' => 'FIL-7', 'name' => 'Filipino 7 (Ibong Adarna)', 'description' => 'JHS Core Subject'],
            ['code' => 'MATH-7', 'name' => 'Mathematics 7 (E.g. Sets, Algebra)', 'description' => 'JHS Core Subject'],
            ['code' => 'SCI-7', 'name' => 'Science 7 (E.g. Diversity of Materials)', 'description' => 'JHS Core Subject'],
            ['code' => 'AP-7', 'name' => 'Araling Panlipunan 7 (Asyanong Pagkakaisa)', 'description' => 'JHS Core Subject'],
            ['code' => 'ESP-7', 'name' => 'Edukasyon sa Pagpapakatao 7', 'description' => 'JHS Core Subject'],

            // Grade 8
            ['code' => 'ENG-8', 'name' => 'English 8 (Afro-Asian Literature)', 'description' => 'JHS Core Subject'],
            ['code' => 'FIL-8', 'name' => 'Filipino 8 (Florante at Laura)', 'description' => 'JHS Core Subject'],
            ['code' => 'MATH-8', 'name' => 'Mathematics 8 (E.g. Linear Equations, Geometry)', 'description' => 'JHS Core Subject'],
            ['code' => 'SCI-8', 'name' => 'Science 8 (E.g. Force, Motion, Energy)', 'description' => 'JHS Core Subject'],
            ['code' => 'AP-8', 'name' => 'Araling Panlipunan 8 (Kasaysayan ng Daigdig)', 'description' => 'JHS Core Subject'],

            // Grade 9
            ['code' => 'ENG-9', 'name' => 'English 9 (Anglo-American Literature)', 'description' => 'JHS Core Subject'],
            ['code' => 'FIL-9', 'name' => 'Filipino 9 (Noli Me Tangere)', 'description' => 'JHS Core Subject'],
            ['code' => 'MATH-9', 'name' => 'Mathematics 9 (E.g. Quadratic Equations, Trigonometry)', 'description' => 'JHS Core Subject'],
            ['code' => 'SCI-9', 'name' => 'Science 9 (E.g. Chemistry, Earth Science)', 'description' => 'JHS Core Subject'],
            ['code' => 'AP-9', 'name' => 'Araling Panlipunan 9 (Ekonomiks)', 'description' => 'JHS Core Subject'],

            // Grade 10
            ['code' => 'ENG-10', 'name' => 'English 10 (Contemporary Literature)', 'description' => 'JHS Core Subject'],
            ['code' => 'FIL-10', 'name' => 'Filipino 10 (El Filibusterismo)', 'description' => 'JHS Core Subject'],
            ['code' => 'MATH-10', 'name' => 'Mathematics 10 (E.g. Statistics, Geometry)', 'description' => 'JHS Core Subject'],
            ['code' => 'SCI-10', 'name' => 'Science 10 (E.g. Biology, Climate Change)', 'description' => 'JHS Core Subject'],
            ['code' => 'AP-10', 'name' => 'Araling Panlipunan 10 (Kontemporaryong Isyu)', 'description' => 'JHS Core Subject'],
            
            // JHS Common Subjects (MAPEH/TLE)
            ['code' => 'MAPEH-7-10', 'name' => 'Music, Arts, Physical Education, and Health (MAPEH)', 'description' => 'JHS Core Subject (General)'],
            ['code' => 'TLE-7-10', 'name' => 'Technology and Livelihood Education (TLE)', 'description' => 'JHS Core Subject (General)'],


            // --- SENIOR HIGH SCHOOL (GRADES 11-12) CORE SUBJECTS ---
            ['code' => 'SHS-OC', 'name' => 'Oral Communication in Context', 'description' => 'SHS Core Subject'],
            ['code' => 'SHS-KP', 'name' => 'Komunikasyon at Pananaliksik sa Wika at Kulturang Pilipino', 'description' => 'SHS Core Subject'],
            ['code' => 'SHS-GM', 'name' => 'General Mathematics', 'description' => 'SHS Core Subject'],
            ['code' => 'SHS-ELS', 'name' => 'Earth and Life Science', 'description' => 'SHS Core Subject'],
            ['code' => 'SHS-PSCI', 'name' => 'Physical Science', 'description' => 'SHS Core Subject'],
            ['code' => 'SHS-IPHP', 'name' => 'Introduction to the Philosophy of the Human Person', 'description' => 'SHS Core Subject'],
            ['code' => 'SHS-CL', 'name' => 'Contemporary Philippine Arts from the Regions', 'description' => 'SHS Core Subject'],
            ['code' => 'SHS-PE', 'name' => 'Physical Education and Health', 'description' => 'SHS Core Subject'],
            ['code' => 'SHS-PRM', 'name' => 'Personal Development / Pansariling Kaunlaran', 'description' => 'SHS Core Subject'],


            // --- SENIOR HIGH SCHOOL (GRADES 11-12) APPLIED SUBJECTS ---
            ['code' => 'SHS-EAPP', 'name' => 'English for Academic and Professional Purposes', 'description' => 'SHS Applied Subject'],
            ['code' => 'SHS-PR1', 'name' => 'Practical Research 1 (Qualitative)', 'description' => 'SHS Applied Subject'],
            ['code' => 'SHS-PR2', 'name' => 'Practical Research 2 (Quantitative)', 'description' => 'SHS Applied Subject'],
            ['code' => 'SHS-FLA', 'name' => 'Filipino sa Piling Larangan', 'description' => 'SHS Applied Subject'],
            ['code' => 'SHS-ETech', 'name' => 'Empowerment Technologies (ICT for Professional Tracks)', 'description' => 'SHS Applied Subject'],
            ['code' => 'SHS-ENTREP', 'name' => 'Entrepreneurship', 'description' => 'SHS Applied Subject'],


            // --- SENIOR HIGH SCHOOL (GRADES 11-12) SPECIALIZED SUBJECTS EXAMPLES ---
            // ABM (Accountancy, Business, and Management)
            ['code' => 'SHS-FABM1', 'name' => 'Fundamentals of Accountancy, Business and Management 1', 'description' => 'SHS Specialized (ABM)'],
            ['code' => 'SHS-ORG', 'name' => 'Organization and Management', 'description' => 'SHS Specialized (ABM)'],
            
            // STEM (Science, Technology, Engineering, and Mathematics)
            ['code' => 'SHS-PC', 'name' => 'Pre-Calculus', 'description' => 'SHS Specialized (STEM)'],
            ['code' => 'SHS-BC', 'name' => 'Basic Calculus', 'description' => 'SHS Specialized (STEM)'],
            ['code' => 'SHS-GBIO', 'name' => 'General Biology 1', 'description' => 'SHS Specialized (STEM)'],
            
            // HUMSS (Humanities and Social Sciences)
            ['code' => 'SHS-DIASS', 'name' => 'Disciplines and Ideas in the Applied Social Sciences', 'description' => 'SHS Specialized (HUMSS)'],
            ['code' => 'SHS-CPAR', 'name' => 'Creative Writing / Malikhaing Pagsulat', 'description' => 'SHS Specialized (HUMSS)'],

            // TVL (Technical-Vocational-Livelihood)
            ['code' => 'SHS-TVL-WELD', 'name' => 'Shielded Metal Arc Welding (NC I)', 'description' => 'SHS Specialized (TVL)'],
            ['code' => 'SHS-TVL-COOK', 'name' => 'Cookery (NC II)', 'description' => 'SHS Specialized (TVL)'],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}