<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LearningPath;
use App\Models\StudentPathProgress;
use App\Services\AdaptiveLearningService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StudentLearningPathController extends Controller
{
    public function __construct(
        private readonly AdaptiveLearningService $adaptiveLearningService,
    ) {}

    /**
     * List available learning paths.
     */
    public function index(): View
    {
        $student = Auth::guard('student')->user();

        $paths = LearningPath::orderBy('title')->get();
        $activePaths = StudentPathProgress::where('student_id', $student->id)
            ->with('learningPath')
            ->get();

        return view('student.learning-paths.index', compact('paths', 'activePaths'));
    }

    /**
     * Show a learning path.
     */
    public function show(LearningPath $path): View
    {
        $path->load('nodes');

        return view('student.learning-paths.show', compact('path'));
    }

    /**
     * Show progress on a learning path.
     */
    public function progress(LearningPath $path): View
    {
        $student = Auth::guard('student')->user();
        $progress = $this->adaptiveLearningService->getProgress($student, $path);

        return view('student.learning-paths.progress', compact('path', 'progress'));
    }

    /**
     * Update progress on a learning path.
     */
    public function updateProgress(LearningPath $path): RedirectResponse
    {
        $student = Auth::guard('student')->user();

        try {
            $this->adaptiveLearningService->adjustDifficulty($student, $path);

            return redirect()
                ->route('student.learning-path.progress', $path)
                ->with('success', 'Learning path progress updated.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update progress: '.$e->getMessage());
        }
    }
}
