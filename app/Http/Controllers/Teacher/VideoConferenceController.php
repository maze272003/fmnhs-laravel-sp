<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Section;
use App\Models\VideoConference;
use App\Services\ConferenceDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VideoConferenceController extends Controller
{
    public function __construct(
        private readonly ConferenceDataService $conferenceDataService,
    ) {}

    public function index(): View
    {
        $teacherId = (int) Auth::guard('teacher')->id();
        $assignedSectionIds = $this->assignedSectionIds($teacherId);

        $sections = Section::whereIn('id', $assignedSectionIds)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $conferences = VideoConference::with('section')
            ->where('teacher_id', $teacherId)
            ->latest()
            ->paginate(12);

        return view('teacher.conference.index', compact('sections', 'conferences'));
    }

    public function store(Request $request): RedirectResponse
    {
        $teacherId = (int) Auth::guard('teacher')->id();
        $assignedSectionIds = $this->assignedSectionIds($teacherId)->values()->all();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'section_id' => ['nullable', 'integer', Rule::in($assignedSectionIds)],
            'visibility' => ['nullable', Rule::in(['public', 'private'])],
            'secret_key' => [
                Rule::requiredIf(fn () => $request->input('visibility') === 'private'),
                'nullable',
                'string',
                'alpha_num',
                'min:6',
                'max:32',
            ],
        ]);

        $visibility = $validated['visibility'] ?? 'public';
        $conference = VideoConference::create([
            'teacher_id' => $teacherId,
            'section_id' => $validated['section_id'] ?? null,
            'title' => $validated['title'],
            'slug' => $this->generateUniqueSlug(),
            'is_active' => true,
            'visibility' => $visibility,
            'secret_key_hash' => $visibility === 'private'
                ? Hash::make((string) $validated['secret_key'])
                : null,
        ]);

        return redirect()
            ->route('teacher.conferences.index')
            ->with('success', 'Meeting link created: '.route('conference.join.form', $conference));
    }

    public function show(VideoConference $conference): RedirectResponse
    {
        $this->ensureOwnedByAuthenticatedTeacher($conference);

        return redirect()->route('conference.room', $conference);
    }

    public function end(Request $request, VideoConference $conference): RedirectResponse|JsonResponse
    {
        $this->ensureOwnedByAuthenticatedTeacher($conference);

        $this->conferenceDataService->endMeeting($conference);

        $conference->update([
            'slug' => $this->generateUniqueSlug(),
            'terminated_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Meeting terminated.',
                'join_link_invalidated' => true,
            ]);
        }

        return redirect()
            ->route('teacher.conferences.index')
            ->with('success', 'Meeting terminated. Old join link is now invalid.');
    }

    public function updatePrivacy(Request $request, VideoConference $conference): RedirectResponse
    {
        $this->ensureOwnedByAuthenticatedTeacher($conference);

        $validated = $request->validate([
            'visibility' => ['required', Rule::in(['public', 'private'])],
            'secret_key' => [
                Rule::requiredIf(fn () => $request->input('visibility') === 'private'),
                'nullable',
                'string',
                'alpha_num',
                'min:6',
                'max:32',
            ],
        ]);

        $conference->update([
            'visibility' => $validated['visibility'],
            'secret_key_hash' => $validated['visibility'] === 'private'
                ? Hash::make((string) $validated['secret_key'])
                : null,
        ]);

        return redirect()
            ->route('teacher.conferences.index')
            ->with('success', 'Room privacy updated.');
    }

    private function assignedSectionIds(int $teacherId): Collection
    {
        $advisorySectionIds = Section::where('teacher_id', $teacherId)->pluck('id');
        $scheduledSectionIds = Schedule::where('teacher_id', $teacherId)->pluck('section_id');

        return $advisorySectionIds->merge($scheduledSectionIds)->unique()->filter();
    }

    private function generateUniqueSlug(): string
    {
        do {
            $slug = Str::lower(Str::random(20));
        } while (VideoConference::where('slug', $slug)->exists());

        return $slug;
    }

    private function ensureOwnedByAuthenticatedTeacher(VideoConference $conference): void
    {
        abort_unless((int) $conference->teacher_id === (int) Auth::guard('teacher')->id(), 403);
    }
}
