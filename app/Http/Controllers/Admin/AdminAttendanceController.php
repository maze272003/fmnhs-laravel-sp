<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Teacher;
use App\Models\Section;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $teachers = Teacher::orderBy('last_name')->get();
        $sections = Section::orderBy('grade_level')->get(); // Get actual Section objects

        $query = Attendance::with(['student', 'teacher', 'subject', 'section']);

        if ($request->filled('date')) $query->where('date', $request->date);
        if ($request->filled('teacher_id')) $query->where('teacher_id', $request->teacher_id);
        if ($request->filled('section_id')) $query->where('section_id', $request->section_id);
        if ($request->filled('status')) $query->where('status', $request->status);

        $records = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();

        return view('admin.attendancelogs', compact('records', 'teachers', 'sections'));
    }
}