<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;

class AdminTeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.manage_teacher', compact('teachers'));
    }
}