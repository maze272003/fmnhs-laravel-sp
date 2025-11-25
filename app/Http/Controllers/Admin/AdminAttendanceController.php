<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Teacher;
use App\Models\Student;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        // 1. Get Filter Options
        $teachers = Teacher::orderBy('last_name')->get();
        $sections = Student::select('section')->distinct()->orderBy('section')->pluck('section');

        // 2. Start Query
        $query = Attendance::with(['student', 'teacher', 'subject']);

        // 3. Apply Filters
        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('section')) {
            $query->where('section', $request->section);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 4. Get Results (Latest first)
        $records = $query->orderBy('date', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->paginate(20)
                         ->withQueryString(); // Para hindi mawala ang filters pag nag next page

        return view('admin.attendancelogs', compact('records', 'teachers', 'sections'));
    }
}