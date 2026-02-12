<?php

namespace App\Services;

use App\Models\LearningPath;
use App\Models\PathNode;
use App\Models\Student;
use App\Models\StudentPathProgress;
use Illuminate\Support\Collection;

class AdaptiveLearningService
{
    /**
     * Create a new learning path.
     */
    public function createLearningPath(array $data): LearningPath
    {
        $path = LearningPath::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'subject_id' => $data['subject_id'] ?? null,
            'difficulty_level' => $data['difficulty_level'] ?? 'beginner',
            'created_by_type' => $data['created_by_type'] ?? null,
            'created_by_id' => $data['created_by_id'] ?? null,
            'is_published' => $data['is_published'] ?? false,
        ]);

        foreach (($data['nodes'] ?? []) as $index => $nodeData) {
            PathNode::create([
                'learning_path_id' => $path->id,
                'title' => $nodeData['title'],
                'content' => $nodeData['content'] ?? null,
                'type' => $nodeData['type'] ?? 'lesson',
                'difficulty' => $nodeData['difficulty'] ?? 'medium',
                'order' => $index + 1,
                'prerequisites' => $nodeData['prerequisites'] ?? [],
                'estimated_minutes' => $nodeData['estimated_minutes'] ?? 15,
            ]);
        }

        return $path->load('nodes');
    }

    /**
     * Assign a learning path to a student.
     */
    public function assignPath(Student $student, LearningPath $path): StudentPathProgress
    {
        $firstNode = $path->nodes()->orderBy('order')->first();

        return StudentPathProgress::updateOrCreate(
            [
                'student_id' => $student->id,
                'learning_path_id' => $path->id,
            ],
            [
                'current_node_id' => $firstNode?->id,
                'completed_nodes' => [],
                'score' => 0,
                'started_at' => now(),
            ]
        );
    }

    /**
     * Get a student's progress on a learning path.
     */
    public function getProgress(Student $student, LearningPath $path): array
    {
        $progress = StudentPathProgress::where('student_id', $student->id)
            ->where('learning_path_id', $path->id)
            ->first();

        if (!$progress) {
            return ['enrolled' => false];
        }

        $totalNodes = $path->nodes()->count();
        $completedCount = count($progress->completed_nodes ?? []);
        $percentage = $totalNodes > 0 ? round(($completedCount / $totalNodes) * 100, 1) : 0;

        return [
            'enrolled' => true,
            'current_node_id' => $progress->current_node_id,
            'completed_nodes' => $progress->completed_nodes ?? [],
            'completed_count' => $completedCount,
            'total_nodes' => $totalNodes,
            'progress_percentage' => $percentage,
            'score' => $progress->score,
            'started_at' => $progress->started_at,
            'completed_at' => $progress->completed_at,
        ];
    }

    /**
     * Recommend the next node for a student based on their progress and performance.
     */
    public function recommendNextNode(Student $student, LearningPath $path): ?PathNode
    {
        $progress = StudentPathProgress::where('student_id', $student->id)
            ->where('learning_path_id', $path->id)
            ->first();

        if (!$progress) {
            return $path->nodes()->orderBy('order')->first();
        }

        $completedIds = $progress->completed_nodes ?? [];

        // Find the next uncompleted node
        $nextNode = $path->nodes()
            ->whereNotIn('id', $completedIds)
            ->orderBy('order')
            ->first();

        if ($nextNode) {
            $progress->update(['current_node_id' => $nextNode->id]);
        }

        return $nextNode;
    }

    /**
     * Adjust the difficulty based on student performance.
     */
    public function adjustDifficulty(Student $student, LearningPath $path): string
    {
        $progress = StudentPathProgress::where('student_id', $student->id)
            ->where('learning_path_id', $path->id)
            ->first();

        if (!$progress) {
            return 'medium';
        }

        $completedNodes = count($progress->completed_nodes ?? []);
        $totalNodes = $path->nodes()->count();

        if ($totalNodes === 0) {
            return 'medium';
        }

        $completionRate = $completedNodes / $totalNodes;
        $score = $progress->score;

        // Adjust based on score and completion speed
        if ($score >= 90 && $completionRate > 0.3) {
            $newDifficulty = 'hard';
        } elseif ($score < 50 || $completionRate < 0.1) {
            $newDifficulty = 'easy';
        } else {
            $newDifficulty = 'medium';
        }

        return $newDifficulty;
    }

    /**
     * Get learning path recommendations for a student.
     */
    public function getRecommendations(Student $student): Collection
    {
        $enrolledPathIds = StudentPathProgress::where('student_id', $student->id)
            ->pluck('learning_path_id');

        return LearningPath::where('is_published', true)
            ->whereNotIn('id', $enrolledPathIds)
            ->orderBy('difficulty_level')
            ->limit(5)
            ->get()
            ->map(fn (LearningPath $p) => [
                'id' => $p->id,
                'title' => $p->title,
                'description' => $p->description,
                'difficulty_level' => $p->difficulty_level,
                'node_count' => $p->nodes()->count(),
            ]);
    }
}
