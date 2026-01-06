<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Schedule;
use App\Models\Announcement;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    /**
     * Dashboard view with dynamic counters
     */
    public function dashboard(): View
    {
        $teacher = Auth::guard('teacher')->user();
        
        // Count unique sections handled via Schedule
        $totalClasses = Schedule::where('teacher_id', $teacher->id)
            ->get()
            ->unique(function($q) { return $q->subject_id . '-' . $q->section_id; })
            ->count();
        
        // Count unique students across all handled sections
        $sectionIds = Schedule::where('teacher_id', $teacher->id)->pluck('section_id')->unique();
        $totalStudents = Student::whereIn('section_id', $sectionIds)->count();
        
        // Fetch Advisory Class using the relationship
        $advisory = Section::where('teacher_id', $teacher->id)->first();
        
        $recentAnnouncements = Announcement::latest()->take(3)->get();

        return view('teacher.dashboard', [
            'totalClasses' => $totalClasses,
            'totalStudents' => $totalStudents,
            'advisoryClass' => $advisory ? "Grade {$advisory->grade_level} - {$advisory->name}" : 'None',
            'recentAnnouncements' => $recentAnnouncements,
            'teacher' => $teacher,
        ]);
    }

    /**
     * FIX: Added missing myClasses method for /teacher/my-classes
     */
    public function myClasses(): View
    {
        $teacherId = Auth::guard('teacher')->id();

        // Fetch all classes where teacher has encoded grades
        $classes = Grade::where('teacher_id', $teacherId)
            ->with(['subject', 'student.section']) 
            ->get()
            ->groupBy(function($data) {
                return $data->subject_id . '-' . $data->student->section_id;
            })
            ->map(function($group) {
                return [
                    'subject' => $group->first()->subject,
                    'section' => $group->first()->student->section,
                    'student_count' => $group->unique('student_id')->count(),
                    'average_grade' => $group->avg('grade_value'),
                ];
            });

        return view('teacher.classes', compact('classes'));
    }

    /**
     * FIX: Added missing myStudents method for /teacher/students
     */
    public function myStudents(Request $request): View
    {
        $sections = Section::orderBy('grade_level')->get();
        $selectedSectionId = $request->section_id;
        $selectedSection = null;
        $students = collect();

        if ($selectedSectionId) {
            $selectedSection = Section::find($selectedSectionId);
            $students = Student::where('section_id', $selectedSectionId)
                        ->orderBy('last_name')
                        ->get();
        }

        return view('teacher.student', compact('sections', 'students', 'selectedSection'));
    }

    public function gradingSheet(): View
    {
        $teacherId = Auth::guard('teacher')->id();

        $assignedClasses = Schedule::where('teacher_id', $teacherId)
            ->with(['subject', 'section'])
            ->get()
            ->unique(function ($item) {
                return $item->subject_id . '-' . $item->section_id;
            });

        return view('teacher.select', compact('assignedClasses'));
    }

    public function showClass(Request $request): View
    {
        $request->validate([
            'subject_id' => 'required',
            'section_id' => 'required'
        ]);

        $subject = Subject::findOrFail($request->subject_id);
        $section = Section::findOrFail($request->section_id);

        $students = Student::where('section_id', $section->id)
                    ->with(['grades' => function($q) use ($subject) {
                        $q->where('subject_id', $subject->id);
                    }])
                    ->orderBy('last_name')
                    ->get();

        return view('teacher.grade', compact('students', 'subject', 'section'));
    }

    public function storeGrades(Request $request): RedirectResponse
    {
        $request->validate([
            'grades' => 'required|array',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $teacherId = Auth::guard('teacher')->id();

        foreach ($request->grades as $studentId => $quarters) {
            foreach ($quarters as $quarter => $value) {
                if ($value === null || $value === '') continue;

                Grade::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => $request->subject_id,
                        'quarter'    => $quarter,
                    ],
                    [
                        'teacher_id'  => $teacherId,
                        'grade_value' => $value,
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Grades archived successfully!');
    }
}