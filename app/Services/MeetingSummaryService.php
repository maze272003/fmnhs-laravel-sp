<?php

namespace App\Services;

use App\Models\MeetingSummary;
use App\Models\VideoConference;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MeetingSummaryService
{
    public function __construct(
        private readonly CaptionService $captionService
    ) {}

    /**
     * Generate a meeting summary for a conference.
     */
    public function generateSummary(VideoConference $conference): MeetingSummary
    {
        $transcript = $this->captionService->getTranscript($conference);
        $fullText = $transcript['full_text'] ?? '';

        $keyPoints = $this->extractKeyPoints($fullText);
        $actionItems = $this->extractActionItems($fullText);
        $summary = $this->buildSummaryText($fullText);

        return MeetingSummary::updateOrCreate(
            ['conference_id' => $conference->id],
            [
                'summary' => $summary,
                'key_points' => $keyPoints,
                'action_items' => $actionItems,
                'transcript' => $fullText,
                'generated_by' => 'system',
            ]
        );
    }

    /**
     * Extract key points from a transcript.
     */
    public function extractKeyPoints(string $transcript): array
    {
        if (empty($transcript)) {
            return [];
        }

        $apiKey = config('services.openai.api_key');
        if ($apiKey) {
            return $this->extractWithAI($transcript, 'key_points');
        }

        // Fallback: split by sentences and take the first sentences as key points
        $sentences = preg_split('/[.!?]+/', $transcript, -1, PREG_SPLIT_NO_EMPTY);
        $sentences = array_map('trim', $sentences);
        $sentences = array_filter($sentences, fn (string $s) => strlen($s) > 20);

        return array_values(array_slice($sentences, 0, 5));
    }

    /**
     * Extract action items from a transcript.
     */
    public function extractActionItems(string $transcript): array
    {
        if (empty($transcript)) {
            return [];
        }

        $apiKey = config('services.openai.api_key');
        if ($apiKey) {
            return $this->extractWithAI($transcript, 'action_items');
        }

        // Fallback: look for action-related keywords
        $actionKeywords = ['should', 'need to', 'will', 'must', 'action', 'todo', 'follow up', 'assign', 'deadline'];
        $sentences = preg_split('/[.!?]+/', $transcript, -1, PREG_SPLIT_NO_EMPTY);
        $actionItems = [];

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            foreach ($actionKeywords as $keyword) {
                if (stripos($sentence, $keyword) !== false && strlen($sentence) > 15) {
                    $actionItems[] = $sentence;
                    break;
                }
            }
        }

        return array_values(array_unique(array_slice($actionItems, 0, 10)));
    }

    /**
     * Get an existing summary for a conference.
     */
    public function getSummary(VideoConference $conference): ?MeetingSummary
    {
        return MeetingSummary::where('conference_id', $conference->id)->first();
    }

    /**
     * Email the meeting summary to recipients.
     */
    public function emailSummary(VideoConference $conference, array $recipients): bool
    {
        $summary = $this->getSummary($conference);
        if (!$summary) {
            $summary = $this->generateSummary($conference);
        }

        try {
            Mail::raw($this->formatSummaryForEmail($summary, $conference), function ($message) use ($recipients, $conference) {
                $message->to($recipients)
                    ->subject("Meeting Summary: {$conference->title}");
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to email meeting summary', [
                'conference_id' => $conference->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Build a summary text from the full transcript.
     */
    protected function buildSummaryText(string $transcript): string
    {
        if (empty($transcript)) {
            return 'No transcript available for summarization.';
        }

        $words = str_word_count($transcript);

        return "Meeting covered approximately {$words} words of discussion. "
            . substr($transcript, 0, 500)
            . (strlen($transcript) > 500 ? '...' : '');
    }

    /**
     * Use AI to extract structured data from transcript.
     */
    protected function extractWithAI(string $transcript, string $type): array
    {
        try {
            $prompt = $type === 'key_points'
                ? 'Extract the key points from this meeting transcript as a JSON array of strings:'
                : 'Extract the action items from this meeting transcript as a JSON array of strings:';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openai.api_key'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-3.5-turbo'),
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => substr($transcript, 0, 4000)],
                ],
                'temperature' => 0.3,
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content', '[]');
                $decoded = json_decode($content, true);

                return is_array($decoded) ? $decoded : [];
            }
        } catch (\Throwable $e) {
            Log::error("AI extraction failed for {$type}", ['error' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * Format the summary for email delivery.
     */
    protected function formatSummaryForEmail(MeetingSummary $summary, VideoConference $conference): string
    {
        $text = "Meeting Summary: {$conference->title}\n";
        $text .= str_repeat('=', 40) . "\n\n";
        $text .= "Summary:\n{$summary->summary}\n\n";

        if (!empty($summary->key_points)) {
            $text .= "Key Points:\n";
            foreach ($summary->key_points as $i => $point) {
                $text .= ($i + 1) . ". {$point}\n";
            }
            $text .= "\n";
        }

        if (!empty($summary->action_items)) {
            $text .= "Action Items:\n";
            foreach ($summary->action_items as $i => $item) {
                $text .= "- {$item}\n";
            }
        }

        return $text;
    }
}
