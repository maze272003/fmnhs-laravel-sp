<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\VideoConference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ConferenceAccessController extends Controller
{
    public function showJoinForm(VideoConference $conference): View|RedirectResponse
    {
        if (Auth::guard('student')->check() && $conference->canStudentJoin(Auth::guard('student')->user())) {
            return redirect()->route('conference.room', $conference);
        }

        return view('conference.join', [
            'conference' => $conference->loadMissing(['teacher', 'section']),
        ]);
    }

    public function joinWithCredentials(Request $request, VideoConference $conference): RedirectResponse
    {
        if (! $conference->is_active || $conference->ended_at !== null) {
            return back()->withErrors([
                'credential' => 'This meeting is already closed.',
            ])->withInput();
        }

        $validated = $request->validate([
            'credential' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $credential = trim($validated['credential']);

        $student = Student::query()
            ->where('email', $credential)
            ->orWhere('lrn', $credential)
            ->first();

        if (! $student || ! Hash::check($validated['password'], $student->password)) {
            return back()->withErrors([
                'credential' => 'Invalid student credentials.',
            ])->withInput();
        }

        Auth::guard('student')->login($student);
        $request->session()->regenerate();

        if (! $conference->canStudentJoin($student)) {
            Auth::guard('student')->logout();

            return back()->withErrors([
                'credential' => 'You are not assigned to this teacher meeting.',
            ])->withInput();
        }

        return redirect()->route('conference.room', $conference);
    }

    public function room(VideoConference $conference): View
    {
        $conference->loadMissing(['teacher', 'section']);
        $teacher = Auth::guard('teacher')->user();
        $student = Auth::guard('student')->user();

        $actorRole = null;
        $actorId = null;
        $actorName = null;

        if ($teacher && (int) $conference->teacher_id === (int) $teacher->id) {
            $actorRole = 'teacher';
            $actorId = 'teacher-'.$teacher->id;
            $actorName = trim($teacher->first_name.' '.$teacher->last_name);
        }

        if (! $actorRole && $student && $conference->canStudentJoin($student)) {
            $actorRole = 'student';
            $actorId = 'student-'.$student->id;
            $actorName = trim($student->first_name.' '.$student->last_name);
        }

        abort_unless($actorRole !== null, 403);

        abort_if(
            $actorRole === 'student' && (! $conference->is_active || $conference->ended_at !== null),
            410,
            'This meeting is closed.'
        );

        if ($actorRole === 'teacher' && $conference->is_active && $conference->started_at === null) {
            $conference->update(['started_at' => now()]);
        }

        return view('conference.room', [
            'conference' => $conference,
            'actorRole' => $actorRole,
            'actorId' => $actorId,
            'actorName' => $actorName ?: ucfirst($actorRole),
            'isMeetingActive' => $conference->is_active && $conference->ended_at === null,
            'joinLink' => route('conference.join.form', $conference),
            'endMeetingUrl' => route('teacher.conferences.end', $conference),
            'backUrl' => $actorRole === 'teacher'
                ? route('teacher.conferences.index')
                : route('student.dashboard'),
            'reverbConfig' => [
                'key' => config('broadcasting.connections.reverb.key'),
                'host' => $this->resolveRealtimeHost(),
                'port' => $this->resolveRealtimePort(),
                'scheme' => $this->resolveRealtimeScheme(),
                'authEndpoint' => '/broadcasting/auth',
                'iceServers' => $this->resolveIceServers(),
            ],
        ]);
    }

    private function resolveRealtimeHost(): string
    {
        $configured = (string) (config('broadcasting.connections.reverb.options.host') ?? '');
        $requestHost = request()->getHost();
        $localHosts = ['localhost', '127.0.0.1', '::1'];

        if ($configured === '') {
            return $requestHost;
        }

        if (! in_array($requestHost, $localHosts, true) && in_array($configured, $localHosts, true)) {
            return $requestHost;
        }

        return $configured;
    }

    private function resolveRealtimeScheme(): string
    {
        $configured = (string) (config('broadcasting.connections.reverb.options.scheme') ?? '');
        if ($configured !== '') {
            return $configured;
        }

        return request()->isSecure() ? 'https' : 'http';
    }

    private function resolveRealtimePort(): int
    {
        $configured = (int) (config('broadcasting.connections.reverb.options.port') ?? 0);
        if ($configured > 0) {
            return $configured;
        }

        return $this->resolveRealtimeScheme() === 'https' ? 443 : 80;
    }

    private function resolveIceServers(): array
    {
        $defaultServers = [
            [
                'urls' => [
                    'stun:stun.l.google.com:19302',
                    'stun:stun1.l.google.com:19302',
                ],
            ],
        ];

        $raw = trim((string) env('WEBRTC_ICE_SERVERS', ''));
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && ! empty($decoded)) {
                $defaultServers = $decoded;
            }
        }

        $turnUrl = trim((string) env('WEBRTC_TURN_URL', ''));
        if ($turnUrl !== '') {
            $turn = ['urls' => [$turnUrl]];

            $turnUser = env('WEBRTC_TURN_USERNAME');
            $turnPass = env('WEBRTC_TURN_CREDENTIAL');
            if ($turnUser !== null && $turnPass !== null && $turnUser !== '' && $turnPass !== '') {
                $turn['username'] = (string) $turnUser;
                $turn['credential'] = (string) $turnPass;
            }

            $defaultServers[] = $turn;
        }

        return $defaultServers;
    }
}
