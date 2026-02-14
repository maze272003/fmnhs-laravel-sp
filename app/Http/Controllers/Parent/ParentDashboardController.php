<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ParentDashboardController extends Controller
{
    /**
     * Parent dashboard.
     */
    public function index(): View
    {
        $parent = Auth::guard('parent')->user();
        $children = $parent->students ?? collect();
        $announcements = Announcement::latest()->take(5)->get();

        return view('parent.dashboard', compact('parent', 'children', 'announcements'));
    }

    /**
     * List parent's children.
     */
    public function children(): View
    {
        $parent = Auth::guard('parent')->user();
        $children = $parent->students ?? collect();

        return view('parent.children', compact('parent', 'children'));
    }

    /**
     * View child's grades.
     */
    public function grades(int $id): View
    {
        $parent = Auth::guard('parent')->user();
        $student = Student::findOrFail($id);

        $grades = $student->grades()
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('parent.grades', compact('student', 'grades'));
    }

    /**
     * View child's attendance.
     */
    public function attendance(int $id): View
    {
        $parent = Auth::guard('parent')->user();
        $student = Student::findOrFail($id);

        $attendance = $student->attendances()
            ->orderBy('date', 'desc')
            ->paginate(30);

        return view('parent.attendance', compact('student', 'attendance'));
    }

    /**
     * View child's schedule.
     */
    public function schedule(int $id): View
    {
        $parent = Auth::guard('parent')->user();
        $student = Student::findOrFail($id);

        return view('parent.schedule', compact('student'));
    }

    /**
     * View child's assignments.
     */
    public function assignments(int $id): View
    {
        $parent = Auth::guard('parent')->user();
        $student = Student::findOrFail($id);

        return view('parent.assignments', compact('student'));
    }

    /**
     * Send a message.
     */
    public function sendMessage(Request $request): RedirectResponse
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        return redirect()->back()->with('success', 'Message sent successfully.');
    }

    /**
     * View messages page.
     */
    public function messages(): View
    {
        $parent = Auth::guard('parent')->user();

        return view('parent.messages', compact('parent'));
    }
}
