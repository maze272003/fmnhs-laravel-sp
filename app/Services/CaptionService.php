<?php

namespace App\Services;

use App\Models\Caption;
use App\Models\VideoConference;
use Illuminate\Support\Collection;

class CaptionService
{
    public function __construct(
        private readonly TranslationService $translationService
    ) {}

    /**
     * Add a caption entry to a conference.
     */
    public function addCaption(
        VideoConference $conference,
        string $text,
        string $language = 'en',
        ?array $speaker = null
    ): Caption {
        return Caption::create([
            'conference_id' => $conference->id,
            'text' => $text,
            'language' => $language,
            'speaker_type' => $speaker['type'] ?? null,
            'speaker_id' => $speaker['id'] ?? null,
            'timestamp_ms' => $conference->started_at
                ? now()->diffInMilliseconds($conference->started_at)
                : 0,
        ]);
    }

    /**
     * Get captions for a conference filtered by language.
     */
    public function getCaptions(VideoConference $conference, string $language = 'en'): Collection
    {
        return Caption::where('conference_id', $conference->id)
            ->where('language', $language)
            ->orderBy('timestamp_ms')
            ->get();
    }

    /**
     * Translate a caption to a target language.
     */
    public function translateCaption(Caption $caption, string $targetLanguage): Caption
    {
        $translated = $this->translationService->translate(
            $caption->text,
            $caption->language,
            $targetLanguage
        );

        return Caption::create([
            'conference_id' => $caption->conference_id,
            'text' => $translated,
            'language' => $targetLanguage,
            'speaker_type' => $caption->speaker_type,
            'speaker_id' => $caption->speaker_id,
            'timestamp_ms' => $caption->timestamp_ms,
        ]);
    }

    /**
     * Get the full transcript for a conference.
     */
    public function getTranscript(VideoConference $conference): array
    {
        $captions = Caption::where('conference_id', $conference->id)
            ->orderBy('timestamp_ms')
            ->get();

        return [
            'conference_id' => $conference->id,
            'conference_title' => $conference->title,
            'duration_ms' => $captions->isNotEmpty()
                ? $captions->last()->timestamp_ms
                : 0,
            'entries' => $captions->map(fn (Caption $c) => [
                'timestamp_ms' => $c->timestamp_ms,
                'text' => $c->text,
                'language' => $c->language,
                'speaker_type' => $c->speaker_type,
                'speaker_id' => $c->speaker_id,
            ])->toArray(),
            'full_text' => $captions->pluck('text')->implode(' '),
        ];
    }
}
