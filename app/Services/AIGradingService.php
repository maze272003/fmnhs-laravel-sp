<?php

namespace App\Services;

use App\Models\AiGradingLog;
use App\Models\Submission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AIGradingService
{
    /**
     * Grade a submission using AI.
     */
    public function gradeSubmission(Submission $submission, ?array $rubric = null): AiGradingLog
    {
        $content = $this->getSubmissionContent($submission);
        $rubric = $rubric ?? $this->getDefaultRubric();

        $result = $this->callAIGradingApi($content, $rubric);

        return AiGradingLog::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'ai_score' => $result['score'] ?? 0,
                'ai_feedback' => $result['feedback'] ?? 'Unable to generate feedback.',
                'rubric_data' => $rubric,
                'status' => $result['score'] !== null ? 'completed' : 'failed',
            ]
        );
    }

    /**
     * Generate detailed feedback for a submission.
     */
    public function generateFeedback(Submission $submission): string
    {
        $content = $this->getSubmissionContent($submission);

        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            return 'AI feedback is not available. Please configure the AI service.';
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-3.5-turbo'),
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an educational grading assistant. Provide constructive, detailed feedback on student submissions.'],
                    ['role' => 'user', 'content' => "Provide feedback for this student submission:\n\n{$content}"],
                ],
                'temperature' => 0.4,
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content', 'No feedback generated.');
            }
        } catch (\Throwable $e) {
            Log::error('AI feedback generation failed', ['error' => $e->getMessage()]);
        }

        return 'Unable to generate AI feedback at this time.';
    }

    /**
     * Check a submission for plagiarism by comparing against other submissions.
     */
    public function checkPlagiarism(Submission $submission): array
    {
        $content = $this->getSubmissionContent($submission);
        $otherSubmissions = Submission::where('assignment_id', $submission->assignment_id)
            ->where('id', '!=', $submission->id)
            ->get();

        $matches = [];
        foreach ($otherSubmissions as $other) {
            $otherContent = $this->getSubmissionContent($other);
            $similarity = $this->calculateSimilarity($content, $otherContent);

            if ($similarity > 0.7) {
                $matches[] = [
                    'submission_id' => $other->id,
                    'student_id' => $other->student_id,
                    'similarity' => round($similarity * 100, 1),
                ];
            }
        }

        return [
            'submission_id' => $submission->id,
            'plagiarism_detected' => !empty($matches),
            'similarity_matches' => $matches,
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the AI grading log for a submission.
     */
    public function getGradingLog(Submission $submission): ?AiGradingLog
    {
        return AiGradingLog::where('submission_id', $submission->id)->first();
    }

    /**
     * Batch grade multiple submissions.
     */
    public function batchGrade(Collection $submissions, ?array $rubric = null): array
    {
        $results = [];

        foreach ($submissions as $submission) {
            $results[] = [
                'submission_id' => $submission->id,
                'student_id' => $submission->student_id,
                'grading_log' => $this->gradeSubmission($submission, $rubric),
            ];
        }

        return [
            'total' => count($results),
            'completed' => collect($results)->filter(fn ($r) => $r['grading_log']->status === 'completed')->count(),
            'failed' => collect($results)->filter(fn ($r) => $r['grading_log']->status === 'failed')->count(),
            'results' => $results,
        ];
    }

    /**
     * Get the content of a submission.
     */
    protected function getSubmissionContent(Submission $submission): string
    {
        if ($submission->remarks) {
            return $submission->remarks;
        }

        if ($submission->file_path && Storage::exists($submission->file_path)) {
            return Storage::get($submission->file_path);
        }

        return '';
    }

    /**
     * Calculate text similarity using a simple approach.
     */
    protected function calculateSimilarity(string $text1, string $text2): float
    {
        if (empty($text1) || empty($text2)) {
            return 0.0;
        }

        similar_text($text1, $text2, $percent);

        return $percent / 100;
    }

    /**
     * Get the default rubric for grading.
     */
    protected function getDefaultRubric(): array
    {
        return [
            ['criterion' => 'Content Quality', 'weight' => 40, 'description' => 'Accuracy and depth of content'],
            ['criterion' => 'Organization', 'weight' => 20, 'description' => 'Logical structure and flow'],
            ['criterion' => 'Grammar & Mechanics', 'weight' => 20, 'description' => 'Writing quality'],
            ['criterion' => 'Creativity', 'weight' => 20, 'description' => 'Original thinking'],
        ];
    }

    /**
     * Call the AI grading API.
     */
    protected function callAIGradingApi(string $content, array $rubric): array
    {
        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            return ['score' => null, 'feedback' => 'AI service not configured.'];
        }

        $rubricText = collect($rubric)->map(fn ($r) => "{$r['criterion']} ({$r['weight']}%): {$r['description']}")->implode("\n");

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-3.5-turbo'),
                'messages' => [
                    ['role' => 'system', 'content' => "Grade this submission using the rubric. Respond with JSON: {\"score\": <0-100>, \"feedback\": \"<text>\"}\n\nRubric:\n{$rubricText}"],
                    ['role' => 'user', 'content' => substr($content, 0, 4000)],
                ],
                'temperature' => 0.3,
            ]);

            if ($response->successful()) {
                $aiContent = $response->json('choices.0.message.content', '{}');
                $decoded = json_decode($aiContent, true);

                return [
                    'score' => $decoded['score'] ?? null,
                    'feedback' => $decoded['feedback'] ?? 'No feedback provided.',
                ];
            }
        } catch (\Throwable $e) {
            Log::error('AI grading API call failed', ['error' => $e->getMessage()]);
        }

        return ['score' => null, 'feedback' => 'AI grading failed.'];
    }
}
