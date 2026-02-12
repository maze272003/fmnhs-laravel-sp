<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LessonPlan;
use App\Services\LessonPlanningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LessonPlanController extends Controller
{
    public function __construct(
        private readonly LessonPlanningService $lessonPlanningService,
    ) {}

    /**
     * List lesson plans.
     */
    public function index(): View
    {
        $teacherId = Auth::guard('teacher')->id();

        $lessonPlans = LessonPlan::where('teacher_id', $teacherId)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('teacher.lesson-plans.index', compact('lessonPlans'));
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        return view('teacher.lesson-plans.create');
    }

    /**
     * Store a new lesson plan.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'grade_level' => ['required', 'string'],
            'duration' => ['nullable', 'integer', 'min:1'],
            'objectives' => ['nullable', 'string'],
            'materials' => ['nullable', 'string'],
            'procedure' => ['nullable', 'string'],
            'assessment' => ['nullable', 'string'],
        ]);

        $validated['teacher_id'] = Auth::guard('teacher')->id();

        $this->lessonPlanningService->createLessonPlan($validated);

        return redirect()
            ->route('teacher.lesson-plans.index')
            ->with('success', 'Lesson plan created successfully.');
    }

    /**
     * Show a lesson plan.
     */
    public function show(LessonPlan $lessonPlan): View
    {
        return view('teacher.lesson-plans.show', compact('lessonPlan'));
    }

    /**
     * Show edit form.
     */
    public function edit(LessonPlan $lessonPlan): View
    {
        return view('teacher.lesson-plans.edit', compact('lessonPlan'));
    }

    /**
     * Update a lesson plan.
     */
    public function update(Request $request, LessonPlan $lessonPlan): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'subject' => ['sometimes', 'string', 'max:255'],
            'grade_level' => ['sometimes', 'string'],
            'duration' => ['nullable', 'integer', 'min:1'],
            'objectives' => ['nullable', 'string'],
            'materials' => ['nullable', 'string'],
            'procedure' => ['nullable', 'string'],
            'assessment' => ['nullable', 'string'],
        ]);

        $this->lessonPlanningService->updateLessonPlan($lessonPlan, $validated);

        return redirect()
            ->route('teacher.lesson-plans.show', $lessonPlan)
            ->with('success', 'Lesson plan updated successfully.');
    }

    /**
     * Delete a lesson plan.
     */
    public function destroy(LessonPlan $lessonPlan): RedirectResponse
    {
        $this->lessonPlanningService->deleteLessonPlan($lessonPlan);

        return redirect()
            ->route('teacher.lesson-plans.index')
            ->with('success', 'Lesson plan deleted successfully.');
    }

    /**
     * Generate a lesson plan using AI.
     */
    public function generateAI(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'topic' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'grade_level' => ['required', 'string'],
            'duration' => ['nullable', 'integer', 'min:1'],
        ]);

        $validated['teacher_id'] = Auth::guard('teacher')->id();

        try {
            $lessonPlan = $this->lessonPlanningService->generateWithAI($validated);

            return redirect()
                ->route('teacher.lesson-plans.show', $lessonPlan)
                ->with('success', 'AI-generated lesson plan created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to generate lesson plan: '.$e->getMessage());
        }
    }
}
