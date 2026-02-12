<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudyGoal;
use App\Services\StudyTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentStudyController extends Controller
{
    public function __construct(
        private readonly StudyTrackingService $studyTrackingService,
    ) {}

    /**
     * Study dashboard.
     */
    public function index(): View
    {
        $student = Auth::guard('student')->user();
        $stats = $this->studyTrackingService->getStats($student);
        $goals = StudyGoal::where('user_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.study.index', compact('stats', 'goals'));
    }

    /**
     * Study timer page.
     */
    public function timer(): View
    {
        return view('student.study.timer');
    }

    /**
     * Study goals page.
     */
    public function goals(): View
    {
        $student = Auth::guard('student')->user();

        $goals = StudyGoal::where('user_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.study.goals', compact('goals'));
    }

    /**
     * Create a study goal.
     */
    public function createGoal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_hours' => ['nullable', 'numeric', 'min:0.5'],
            'target_date' => ['nullable', 'date', 'after:today'],
        ]);

        $student = Auth::guard('student')->user();

        try {
            $this->studyTrackingService->createGoal($student, $validated);

            return redirect()
                ->route('student.study.goals')
                ->with('success', 'Study goal created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to create goal: '.$e->getMessage());
        }
    }
}
