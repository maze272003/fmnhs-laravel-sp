<?php

namespace App\Http\Controllers\Admin;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class AdminStudentController extends Controller
{
    public function index()
    {
        // Kukunin ang students, 10 per page
        $students = Student::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.manage_student', compact('students'));
    }
}