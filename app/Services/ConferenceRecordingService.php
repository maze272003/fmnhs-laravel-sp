<?php

namespace App\Services;

use App\Models\ConferenceRecording;
use App\Models\VideoConference;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ConferenceRecordingService
{
    public function __construct(
        private readonly ConferenceDataService $dataService,
    ) {}

    /**
     * Store a recording upload (from client-side MediaRecorder).
     */
    public function storeRecording(
        VideoConference $conference,
        UploadedFile $file,
        string $type = 'video',
        ?string $title = null,
    ): ConferenceRecording {
        $disk = config('conference.recording_disk', 's3');
        $extension = $file->getClientOriginalExtension() ?: 'webm';
        $fileName = Str::slug($conference->title).'-'.now()->format('Ymd-His').'.'.$extension;
        $path = "conference-recordings/{$conference->id}/{$fileName}";

        Storage::disk($disk)->put($path, $file->getContent());

        $recording = ConferenceRecording::create([
            'conference_id' => $conference->id,
            'title' => $title ?? $conference->title.' - Recording',
            'disk' => $disk,
            'file_path' => $path,
            'file_name' => $fileName,
            'mime_type' => $file->getMimeType() ?? ($type === 'audio' ? 'audio/webm' : 'video/webm'),
            'file_size' => $file->getSize(),
            'duration_seconds' => $conference->getElapsedSeconds(),
            'type' => $type,
            'status' => 'ready',
            'restricted' => true,
            'ready_at' => now(),
        ]);

        $this->dataService->logEvent($conference, null, 'recording_saved', [
            'recording_id' => $recording->id,
            'type' => $type,
            'file_size' => $file->getSize(),
        ]);

        return $recording;
    }

    /**
     * Store a transcript for a recording.
     */
    public function storeTranscript(ConferenceRecording $recording, string $transcriptContent): void
    {
        $disk = $recording->disk;
        $path = "conference-recordings/{$recording->conference_id}/transcript-{$recording->id}.txt";

        Storage::disk($disk)->put($path, $transcriptContent);
        $recording->update(['transcript_path' => $path]);
    }

    /**
     * Add chapter markers to a recording.
     */
    public function addChapters(ConferenceRecording $recording, array $chapters): void
    {
        $recording->update(['chapters' => $chapters]);
    }

    /**
     * Auto-generate chapters from conference events (join/leave/screen share).
     */
    public function autoGenerateChapters(ConferenceRecording $recording): array
    {
        $conference = $recording->conference;
        $events = $conference->events()
            ->whereIn('event_type', ['join', 'leave', 'screen_share_start', 'screen_share_stop', 'recording_start', 'meeting_ended'])
            ->orderBy('conference_elapsed_seconds')
            ->get();

        $chapters = [];
        $chapters[] = [
            'time' => 0,
            'title' => 'Meeting Start',
        ];

        foreach ($events as $event) {
            $title = match ($event->event_type) {
                'join' => ($event->metadata['display_name'] ?? 'Someone').' joined',
                'leave' => ($event->metadata['display_name'] ?? 'Someone').' left',
                'screen_share_start' => 'Screen sharing started',
                'screen_share_stop' => 'Screen sharing ended',
                'meeting_ended' => 'Meeting ended',
                default => ucfirst(str_replace('_', ' ', $event->event_type)),
            };

            $chapters[] = [
                'time' => $event->conference_elapsed_seconds,
                'title' => $title,
            ];
        }

        $this->addChapters($recording, $chapters);

        return $chapters;
    }

    /**
     * Download a recording (generate temporary URL).
     */
    public function getDownloadUrl(ConferenceRecording $recording): ?string
    {
        return $recording->getDownloadUrl();
    }

    /**
     * Download transcript.
     */
    public function getTranscriptContent(ConferenceRecording $recording): ?string
    {
        if (! $recording->transcript_path) {
            return null;
        }

        return Storage::disk($recording->disk)->get($recording->transcript_path);
    }

    /**
     * Get recording with synchronized chat replay data.
     */
    public function getRecordingWithChatReplay(ConferenceRecording $recording): array
    {
        $conference = $recording->conference;

        return [
            'recording' => [
                'id' => $recording->id,
                'title' => $recording->title,
                'type' => $recording->type,
                'duration_seconds' => $recording->duration_seconds,
                'mime_type' => $recording->mime_type,
                'file_size' => $recording->file_size,
                'chapters' => $recording->chapters ?? [],
                'download_url' => $recording->getDownloadUrl(),
                'has_transcript' => $recording->transcript_path !== null,
            ],
            'chat_replay' => $this->dataService->getChatReplay($conference),
            'events' => $this->dataService->getEventTimeline($conference),
        ];
    }

    /**
     * Delete a recording from storage and database.
     */
    public function deleteRecording(ConferenceRecording $recording): void
    {
        Storage::disk($recording->disk)->delete($recording->file_path);

        if ($recording->transcript_path) {
            Storage::disk($recording->disk)->delete($recording->transcript_path);
        }

        $recording->delete();
    }
}
