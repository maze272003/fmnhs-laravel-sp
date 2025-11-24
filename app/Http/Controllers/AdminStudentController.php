<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class AdminStudentController extends Controller
{
    public function index()
    {
        // Kukunin ang students, 10 per page
        $students = Student::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.manage_student', compact('students'));
    }
}