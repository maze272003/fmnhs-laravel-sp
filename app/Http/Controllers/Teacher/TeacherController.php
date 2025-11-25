<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Grade;
use Illuminate\Support\Facades\Auth;
use App\Models\Schedule;
use App\Models\Announcement;

class TeacherController extends Controller
{
    public function dashboard()
    {
        $teacher = Auth::guard('teacher')->user();

        // --- Dynamic Data Fetching (Simulated) ---
        // In reality, these should be calculated based on the teacher's assignments in the database.
        
        // 1. Total Classes Assigned (e.g., total unique subject/section combinations)
        // If teacher is advisory for G10-Rizal and teaches Math to G10-Rizal and G9-Acacia, totalClasses = 2.
        $totalClasses = 6; // Placeholder Value
        
        // 2. Total Unique Students across all assigned classes
        $totalStudents = 240; // Placeholder Value
        
        // 3. Advisory Class (Assuming the Teacher model has an 'advisory_class' column)
        // If the column doesn't exist, this should be fetched from a relationship.
        $advisoryClass = $teacher->advisory_class ?? 'G10 - Rizal (N/A)'; // Placeholder/Fallback
        
        // You can fetch recent announcements here if needed
        $recentAnnouncements = Announcement::latest()->take(3)->get();
        
        $data = [
            'totalClasses' => $totalClasses,
            'totalStudents' => $totalStudents,
            'advisoryClass' => $advisoryClass,
            'recentAnnouncements' => $recentAnnouncements,
            'teacher' => $teacher, // Pass the teacher object for easy access
        ];

        return view('teacher.dashboard', $data);
    }
    // 1. Show the "Selector" page (Pick Subject & Section)
    public function gradingSheet()
    {
        $teacherId = Auth::guard('teacher')->id();

        // NEW LOGIC: Fetch assigned classes only based on Schedule
        // Group by Subject+Section to avoid duplicates if multiple schedule days exist
        $assignedClasses = Schedule::where('teacher_id', $teacherId)
            ->with('subject')
            ->get()
            ->unique(function ($item) {
                return $item->subject_id . '-' . $item->section;
            });

        // Ipapasa natin ito sa view sa halip na hiwalay na subjects/sections
        return view('teacher.select', compact('assignedClasses'));
    }
    public function myStudents(Request $request)
    {
        // 1. Kunin lahat ng Sections para sa dropdown
        $sections = Student::select('section')->distinct()->orderBy('section')->pluck('section');

        // 2. Check kung may piniling section ang teacher
        $selectedSection = $request->section;
        $students = collect(); // Empty muna default

        if ($selectedSection) {
            // Kunin ang students sa section na 'yun
            $students = Student::where('section', $selectedSection)
                        ->orderBy('last_name')
                        ->get();
        }

        return view('teacher.student', compact('sections', 'students', 'selectedSection'));
    }
    public function myClasses()
    {
        $teacherId = Auth::guard('teacher')->id();

        // Logic: Kunin lahat ng grades na binigay ni Teacher
        // I-group natin base sa (Subject ID + Section Name) para makuha ang unique classes
        $classes = Grade::where('teacher_id', $teacherId)
            ->with(['subject', 'student']) // Eager load para mabilis
            ->get()
            ->groupBy(function($data) {
                return $data->subject->id . '-' . $data->student->section;
            })
            ->map(function($group) {
                // Gumawa ng summary para sa bawat class
                return [
                    'subject' => $group->first()->subject,
                    'section' => $group->first()->student->section,
                    'student_count' => $group->unique('student_id')->count(),
                    'average_grade' => $group->avg('grade_value'),
                    'last_updated' => $group->max('updated_at')
                ];
            });

        return view('teacher.classes', compact('classes'));
    }

    // 2. Show the actual students for grading
    public function showClass(Request $request)
    {
        $request->validate([
            'subject_id' => 'required',
            'section' => 'required'
        ]);

        $subject = Subject::findOrFail($request->subject_id);
        $section = $request->section;

        // Get students in this section
        $students = Student::where('section', $section)
                    ->orderBy('last_name')
                    ->get();

        // Pass the teacher's ID for saving later
        $teacherId = Auth::guard('teacher')->id();

        return view('teacher.grade', compact('students', 'subject', 'section', 'teacherId'));
    }
    public function storeGrades(Request $request)
    {
        // Data structure: grades[student_id][quarter] = value
        $data = $request->validate([
            'grades' => 'required|array',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $teacherId = Auth::guard('teacher')->id();
        $subjectId = $request->subject_id;

        foreach ($request->grades as $studentId => $quarters) {
            foreach ($quarters as $quarter => $value) {
                
                // Skip kung walang laman ang input
                if ($value === null || $value === '') continue;

                // Update or Create logic
                Grade::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => $subjectId,
                        'quarter'    => $quarter,
                    ],
                    [
                        'teacher_id'  => $teacherId, // Update who edited last
                        'grade_value' => $value,
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Grades successfully saved!');
    }
}