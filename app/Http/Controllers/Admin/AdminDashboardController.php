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
        // 1. Basic Stats Cards
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalSubjects = Subject::count();

        // 2. Data for Pie Chart (Students per Grade Level)
        // Result: [{grade_level: 11, total: 50}, {grade_level: 12, total: 40}]
        $studentsPerGrade = Student::select('grade_level', DB::raw('count(*) as total'))
            ->groupBy('grade_level')
            ->orderBy('grade_level')
            ->get();

        // 3. Data for Bar Chart (Teachers per Department)
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