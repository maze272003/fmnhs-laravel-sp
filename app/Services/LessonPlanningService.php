<?php

namespace App\Services;

use App\Models\LessonPlan;
use App\Models\LessonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LessonPlanningService
{
    /**
     * Create a new lesson plan.
     */
    public function create(array $data): LessonPlan
    {
        $lessonPlan = LessonPlan::create([
            'teacher_id' => $data['teacher_id'],
            'subject_id' => $data['subject_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'objectives' => $data['objectives'] ?? [],
            'activities' => $data['activities'] ?? [],
            'resources' => $data['resources'] ?? [],
            'duration_minutes' => $data['duration_minutes'] ?? 60,
            'grade_level' => $data['grade_level'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'scheduled_date' => $data['scheduled_date'] ?? null,
        ]);

        // Attach resources if provided
        foreach (($data['lesson_resources'] ?? []) as $resourceData) {
            LessonResource::create([
                'lesson_plan_id' => $lessonPlan->id,
                'title' => $resourceData['title'],
                'type' => $resourceData['type'] ?? 'link',
                'file_path' => $resourceData['file_path'] ?? null,
                'url' => $resourceData['url'] ?? null,
                'description' => $resourceData['description'] ?? null,
            ]);
        }

        return $lessonPlan->load('lessonResources');
    }

    /**
     * Generate a lesson plan using AI.
     */
    public function generateWithAI(string $subject, string $topic, ?int $gradeLevel = null): array
    {
        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            return $this->getFallbackLessonPlan($subject, $topic, $gradeLevel);
        }

        $gradeText = $gradeLevel ? "Grade {$gradeLevel}" : 'general level';

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-3.5-turbo'),
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an expert lesson plan designer. Generate lesson plans in JSON format with keys: title, description, objectives (array), activities (array of {name, description, duration_minutes}), resources (array of strings), duration_minutes.'],
                    ['role' => 'user', 'content' => "Create a lesson plan for {$subject} on the topic \"{$topic}\" for {$gradeText}."],
                ],
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content', '{}');
                $decoded = json_decode($content, true);

                return is_array($decoded) ? $decoded : $this->getFallbackLessonPlan($subject, $topic, $gradeLevel);
            }
        } catch (\Throwable $e) {
            Log::error('AI lesson plan generation failed', ['error' => $e->getMessage()]);
        }

        return $this->getFallbackLessonPlan($subject, $topic, $gradeLevel);
    }

    /**
     * Get suggestions for improving a lesson plan.
     */
    public function getSuggestions(LessonPlan $lessonPlan): array
    {
        $suggestions = [];

        if (empty($lessonPlan->objectives)) {
            $suggestions[] = 'Add learning objectives to define clear outcomes.';
        }

        if (empty($lessonPlan->activities)) {
            $suggestions[] = 'Include activities to engage students.';
        }

        if ($lessonPlan->duration_minutes < 30) {
            $suggestions[] = 'Consider extending the lesson duration for deeper coverage.';
        }

        if (empty($lessonPlan->resources)) {
            $suggestions[] = 'Add supplementary resources or materials.';
        }

        if ($lessonPlan->lessonResources()->count() === 0) {
            $suggestions[] = 'Attach lesson resources such as worksheets or presentations.';
        }

        if (!$lessonPlan->grade_level) {
            $suggestions[] = 'Specify a grade level to tailor the content.';
        }

        return $suggestions;
    }

    /**
     * Get lesson plan templates, optionally filtered by subject.
     */
    public function getTemplates(?string $subject = null): array
    {
        $templates = [
            [
                'name' => '5E Model',
                'subject' => 'Science',
                'structure' => ['Engage', 'Explore', 'Explain', 'Elaborate', 'Evaluate'],
                'duration_minutes' => 60,
            ],
            [
                'name' => 'Direct Instruction',
                'subject' => null,
                'structure' => ['Introduction', 'Presentation', 'Guided Practice', 'Independent Practice', 'Assessment'],
                'duration_minutes' => 50,
            ],
            [
                'name' => 'Problem-Based Learning',
                'subject' => 'Math',
                'structure' => ['Present Problem', 'Research', 'Develop Solutions', 'Present Findings', 'Reflect'],
                'duration_minutes' => 90,
            ],
            [
                'name' => 'Workshop Model',
                'subject' => 'English',
                'structure' => ['Mini-Lesson', 'Work Time', 'Conferring', 'Sharing'],
                'duration_minutes' => 60,
            ],
        ];

        if ($subject) {
            return array_values(array_filter($templates, fn ($t) => $t['subject'] === null || strcasecmp($t['subject'], $subject) === 0));
        }

        return $templates;
    }

    /**
     * Duplicate a lesson plan.
     */
    public function duplicate(LessonPlan $lessonPlan): LessonPlan
    {
        $copy = $lessonPlan->replicate();
        $copy->title = $lessonPlan->title . ' (Copy)';
        $copy->status = 'draft';
        $copy->scheduled_date = null;
        $copy->save();

        foreach ($lessonPlan->lessonResources as $resource) {
            $resourceCopy = $resource->replicate();
            $resourceCopy->lesson_plan_id = $copy->id;
            $resourceCopy->save();
        }

        return $copy->load('lessonResources');
    }

    /**
     * Fallback lesson plan when AI is unavailable.
     */
    protected function getFallbackLessonPlan(string $subject, string $topic, ?int $gradeLevel): array
    {
        return [
            'title' => "{$topic} - {$subject}",
            'description' => "A lesson plan covering {$topic} in {$subject}.",
            'objectives' => [
                "Understand the key concepts of {$topic}",
                "Apply knowledge of {$topic} in practical exercises",
            ],
            'activities' => [
                ['name' => 'Introduction', 'description' => "Introduce {$topic} with examples", 'duration_minutes' => 10],
                ['name' => 'Discussion', 'description' => 'Class discussion on key concepts', 'duration_minutes' => 15],
                ['name' => 'Activity', 'description' => 'Hands-on exercise', 'duration_minutes' => 20],
                ['name' => 'Wrap-up', 'description' => 'Summary and questions', 'duration_minutes' => 10],
            ],
            'resources' => ['Textbook', 'Whiteboard', 'Handouts'],
            'duration_minutes' => 55,
            'grade_level' => $gradeLevel,
        ];
    }
}
