<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalSubjects = Subject::count();

        // Join with sections table because grade_level is moved there
        $studentsPerGrade = Student::join('sections', 'students.section_id', '=', 'sections.id')
            ->select('sections.grade_level', DB::raw('count(*) as total'))
            ->groupBy('sections.grade_level')
            ->orderBy('sections.grade_level')
            ->get();

        $teachersPerDept = Teacher::select('department', DB::raw('count(*) as total'))
            ->groupBy('department')
            ->get();

        return view('admin.dashboard', compact(
            'totalStudents', 
            'totalTeachers', 
            'totalSubjects',
            'studentsPerGrade',
            'teachersPerDept'
        ));
    }
}