<?php

namespace App\Http\Controllers;

use App\Models\ConferenceRecording;
use App\Models\VideoConference;
use App\Services\ConferenceRecordingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ConferencePlaybackController extends Controller
{
    public function __construct(
        private readonly ConferenceRecordingService $recordingService,
    ) {}

    /**
     * Show the recording playback page with synced chat replay.
     */
    public function show(VideoConference $conference, ConferenceRecording $recording): View
    {
        $teacher = Auth::guard('teacher')->user();
        $student = Auth::guard('student')->user();

        $authorized = false;
        if ($teacher && (int) $conference->teacher_id === (int) $teacher->id) {
            $authorized = true;
        }
        if ($student && ! $recording->restricted) {
            $authorized = true;
        }

        abort_unless($authorized, 403);
        abort_unless((int) $recording->conference_id === (int) $conference->id, 404);

        $playbackData = $this->recordingService->getRecordingWithChatReplay($recording);

        return view('conference.playback', [
            'conference' => $conference->loadMissing(['teacher', 'section']),
            'recording' => $recording,
            'playbackData' => $playbackData,
        ]);
    }
}
