<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudyGoal;
use App\Models\StudySession;
use App\Models\Subject;
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
        $stats = $this->studyTrackingService->getStudyStats($student);
        $goals = StudyGoal::where('student_id', $student->id)
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

        $goals = StudyGoal::where('student_id', $student->id)
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
            'target_minutes' => ['nullable', 'integer', 'min:1'],
            'period' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date', 'after:today'],
        ]);

        $student = Auth::guard('student')->user();

        try {
            $this->studyTrackingService->createGoal($student, $validated);

            return redirect()
                ->route('student.study.index')
                ->with('success', 'Study goal created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to create goal: '.$e->getMessage());
        }
    }

    /**
     * Start a study session.
     */
    public function startSession(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
        ]);

        $student = Auth::guard('student')->user();
        $subject = ! empty($validated['subject_id']) ? Subject::find($validated['subject_id']) : null;

        try {
            $this->studyTrackingService->startSession(
                $student,
                $validated['type'] ?? 'pomodoro',
                $subject
            );

            return redirect()
                ->route('student.study.index')
                ->with('success', 'Study session started.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to start session: '.$e->getMessage());
        }
    }

    /**
     * End a study session.
     */
    public function endSession(StudySession $session): RedirectResponse
    {
        try {
            $this->studyTrackingService->endSession($session);

            return redirect()
                ->route('student.study.index')
                ->with('success', 'Study session ended.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to end session: '.$e->getMessage());
        }
    }

    /**
     * Store a study goal.
     */
    public function storeGoal(Request $request): RedirectResponse
    {
        return $this->createGoal($request);
    }

    /**
     * Update a study goal.
     */
    public function updateGoal(Request $request, StudyGoal $goal): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'target_minutes' => ['nullable', 'integer', 'min:1'],
            'period' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date', 'after:today'],
        ]);

        try {
            $goal->update($validated);

            return redirect()
                ->route('student.study.index')
                ->with('success', 'Study goal updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update goal: '.$e->getMessage());
        }
    }
}
