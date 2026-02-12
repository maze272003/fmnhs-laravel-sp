<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LearningPath;
use App\Services\AdaptiveLearningService;
use Illuminate\Support\Facades\Auth;
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
        $activePaths = $this->adaptiveLearningService->getStudentPaths($student);

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
        $progress = $this->adaptiveLearningService->getProgress($path, $student);

        return view('student.learning-paths.progress', compact('path', 'progress'));
    }
}
