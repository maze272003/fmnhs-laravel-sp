<?php
namespace App\Http\Controllers\Admin;

use App\Models\Teacher;
use App\Http\Controllers\Controller;

class AdminTeacherController extends Controller
{
    public function index()
    {
        // Eager load advisorySection to see which section they manage
        $teachers = Teacher::with('advisorySection')->orderBy('last_name')->paginate(10);
        return view('admin.manage_teacher', compact('teachers'));
    }
}