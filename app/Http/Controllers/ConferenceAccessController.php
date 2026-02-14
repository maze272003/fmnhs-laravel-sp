<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\VideoConference;
use App\Support\ConferenceSignalingToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ConferenceAccessController extends Controller
{
    public function showJoinForm(Request $request, VideoConference $conference): View|RedirectResponse
    {
        if ($request->boolean('reset_guest')) {
            $this->clearGuestIdentity($request, $conference);
            $this->clearSecretValidation($request, $conference);
        }

        if (
            Auth::guard('student')->check()
            && $conference->canStudentJoin(Auth::guard('student')->user())
            && $this->studentPassesSecretPolicy($request, $conference)
        ) {
            return redirect()->route('conference.room', $conference);
        }

        if ($this->resolveGuestIdentity($request, $conference) !== null && $conference->is_active && $conference->ended_at === null) {
            return redirect()->route('conference.room', $conference);
        }

        return view('conference.join', [
            'conference' => $conference->loadMissing(['teacher', 'section']),
            'requiresSecretKey' => $conference->requiresSecretKey(),
            'guestKeyValidated' => $this->hasValidatedSecret($request, $conference),
            'supportsGuestJoin' => $conference->isPrivateRoom() && $conference->hasSecretKey(),
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
            'secret_key' => $conference->requiresSecretKey()
                ? ['required', 'string', 'alpha_num', 'min:6', 'max:32']
                : ['nullable', 'string'],
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

        if ($conference->requiresSecretKey() && ! $conference->verifySecretKey($validated['secret_key'] ?? null)) {
            return back()->withErrors([
                'secret_key' => 'Invalid secret key.',
            ])->withInput();
        }

        Auth::guard('student')->login($student);
        $request->session()->regenerate();

        if ($conference->requiresSecretKey()) {
            $this->markSecretValidated($request, $conference);
        }

        if (! $conference->canStudentJoin($student)) {
            Auth::guard('student')->logout();

            return back()->withErrors([
                'credential' => 'You are not assigned to this teacher meeting.',
            ])->withInput();
        }

        return redirect()->route('conference.room', $conference);
    }

    public function validateGuestKey(Request $request, VideoConference $conference): RedirectResponse
    {
        if (! $conference->isPrivateRoom() || ! $conference->hasSecretKey()) {
            return back()->withErrors([
                'guest_secret_key' => 'Guest access is disabled for this room.',
            ]);
        }

        $validated = $request->validate([
            'guest_secret_key' => ['required', 'string', 'alpha_num', 'min:6', 'max:32'],
        ]);

        if (! $conference->verifySecretKey($validated['guest_secret_key'])) {
            return back()->withErrors([
                'guest_secret_key' => 'Invalid secret key.',
            ])->withInput();
        }

        $this->markSecretValidated($request, $conference);

        return redirect()
            ->route('conference.join.form', $conference)
            ->with('guest_key_validated', true);
    }

    public function joinAsGuest(Request $request, VideoConference $conference): RedirectResponse
    {
        if (! $conference->is_active || $conference->ended_at !== null) {
            return back()->withErrors([
                'temporary_name' => 'This meeting is already closed.',
            ]);
        }

        if (! $conference->isPrivateRoom() || ! $conference->hasSecretKey()) {
            return back()->withErrors([
                'temporary_name' => 'Guest entry is not available for this room.',
            ]);
        }

        if (! $this->hasValidatedSecret($request, $conference)) {
            return back()->withErrors([
                'guest_secret_key' => 'Validate the secret key before entering your name.',
            ]);
        }

        $validated = $request->validate([
            'temporary_name' => ['required', 'string', 'min:2', 'max:40', 'regex:/^[A-Za-z0-9 ._\-]+$/'],
        ]);

        $name = Str::of($validated['temporary_name'])->squish()->trim()->toString();

        $request->session()->put($this->guestIdentitySessionKey($conference), [
            'id' => 'guest-'.Str::lower(Str::random(16)),
            'name' => $name,
            'issued_at' => now()->toIso8601String(),
        ]);

        return redirect()->route('conference.room', $conference);
    }

    public function room(Request $request, VideoConference $conference): View
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

        if (! $actorRole && $student && $conference->canStudentJoin($student) && $this->studentPassesSecretPolicy($request, $conference)) {
            $actorRole = 'student';
            $actorId = 'student-'.$student->id;
            $actorName = trim($student->first_name.' '.$student->last_name);
        }

        $guestIdentity = $this->resolveGuestIdentity($request, $conference);
        if (
            ! $actorRole
            && $guestIdentity !== null
            && $conference->isPrivateRoom()
            && $conference->hasSecretKey()
            && $conference->is_active
            && $conference->ended_at === null
        ) {
            $actorRole = 'guest';
            $actorId = (string) $guestIdentity['id'];
            $actorName = (string) $guestIdentity['name'];
        }

        abort_unless($actorRole !== null, 403);

        abort_if(
            $actorRole !== 'teacher' && (! $conference->is_active || $conference->ended_at !== null),
            410,
            'This meeting is closed.'
        );

        if ($actorRole === 'teacher' && $conference->is_active && $conference->started_at === null) {
            $conference->update(['started_at' => now()]);
        }

        return view('conference.room-v2', [
            'conference' => $conference,
            'actorRole' => $actorRole,
            'actorId' => $actorId,
            'actorName' => $actorName ?: ucfirst($actorRole),
            'isMeetingActive' => $conference->is_active && $conference->ended_at === null,
            'joinLink' => route('conference.join.form', $conference),
            'endMeetingUrl' => route('teacher.conferences.end', $conference),
            'statusUrl' => route('conference.status', $conference),
            'backUrl' => $actorRole === 'teacher'
                ? route('teacher.conferences.index')
                : ($actorRole === 'guest' ? url('/') : route('student.dashboard')),
            'signalingConfig' => [
                'url' => config('app.conference_signaling_url') ?? 'wss://ws.fmnhs-stg.hostcluster.site',
                'roomId' => "conference-{$conference->id}",
                'token' => ConferenceSignalingToken::issue([
                    'conferenceId' => (int) $conference->id,
                    'actorId' => (string) $actorId,
                    'actorName' => (string) ($actorName ?: ucfirst($actorRole)),
                    'actorRole' => (string) $actorRole,
                ]),
                'iceServers' => $this->resolveIceServers(),
            ],
        ]);
    }

    public function status(Request $request, VideoConference $conference): JsonResponse
    {
        $teacher = Auth::guard('teacher')->user();
        $student = Auth::guard('student')->user();
        $guestIdentity = $this->resolveGuestIdentity($request, $conference);

        $authorized = ($teacher && (int) $conference->teacher_id === (int) $teacher->id)
            || ($student && $conference->canStudentJoin($student) && $this->studentPassesSecretPolicy($request, $conference))
            || ($guestIdentity !== null && $conference->isPrivateRoom() && $conference->hasSecretKey());

        abort_unless($authorized, 403);

        return response()->json([
            'is_active' => (bool) ($conference->is_active && $conference->ended_at === null),
            'ended_at' => $conference->ended_at?->toIso8601String(),
            'terminated_at' => $conference->terminated_at?->toIso8601String(),
            'visibility' => $conference->visibility,
        ]);
    }

    private function buildSignalingUrl(): string
    {
        $host = $this->resolveSignalingHost();
        $port = $this->resolveSignalingPort();
        $scheme = $this->resolveSignalingScheme();
        $path = $this->resolveSignalingPath();

        return "{$scheme}://{$host}:{$port}{$path}";
    }

    private function resolveSignalingHost(): string
    {
        $configured = trim((string) config('conference_signaling.server.host', ''));
        $requestHost = (string) request()->getHost();
        $localHosts = ['localhost', '127.0.0.1', '::1', '[::1]'];

        if ($configured === '' || $configured === '0.0.0.0') {
            return $requestHost;
        }

        if (! in_array($requestHost, $localHosts, true) && in_array($configured, $localHosts, true)) {
            return $requestHost;
        }

        return $configured;
    }

    private function resolveSignalingScheme(): string
    {
        $configured = strtolower(trim((string) config('conference_signaling.server.scheme', '')));
        if (in_array($configured, ['ws', 'wss'], true)) {
            return $configured;
        }

        return request()->isSecure() ? 'wss' : 'ws';
    }

    private function resolveSignalingPort(): int
    {
        $configured = (int) config('conference_signaling.server.port', 6001);
        if ($configured > 0) {
            return $configured;
        }

        return $this->resolveSignalingScheme() === 'wss' ? 443 : 80;
    }

    private function resolveSignalingPath(): string
    {
        $path = trim((string) config('conference_signaling.server.path', '/ws/conference'));

        if ($path === '') {
            return '/';
        }

        return str_starts_with($path, '/') ? $path : '/'.$path;
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

    private function studentPassesSecretPolicy(Request $request, VideoConference $conference): bool
    {
        if (! $conference->requiresSecretKey()) {
            return true;
        }

        return $this->hasValidatedSecret($request, $conference);
    }

    private function markSecretValidated(Request $request, VideoConference $conference): void
    {
        $request->session()->put($this->secretValidationSessionKey($conference), now()->timestamp);
    }

    private function hasValidatedSecret(Request $request, VideoConference $conference): bool
    {
        $value = $request->session()->get($this->secretValidationSessionKey($conference));

        return is_numeric($value);
    }

    private function clearSecretValidation(Request $request, VideoConference $conference): void
    {
        $request->session()->forget($this->secretValidationSessionKey($conference));
    }

    private function resolveGuestIdentity(Request $request, VideoConference $conference): ?array
    {
        $identity = $request->session()->get($this->guestIdentitySessionKey($conference));
        if (! is_array($identity)) {
            return null;
        }

        $id = trim((string) ($identity['id'] ?? ''));
        $name = trim((string) ($identity['name'] ?? ''));

        if ($id === '' || $name === '') {
            return null;
        }

        return [
            'id' => $id,
            'name' => $name,
        ];
    }

    private function clearGuestIdentity(Request $request, VideoConference $conference): void
    {
        $request->session()->forget($this->guestIdentitySessionKey($conference));
    }

    private function secretValidationSessionKey(VideoConference $conference): string
    {
        return 'conference.secret_validated.'.$conference->id;
    }

    private function guestIdentitySessionKey(VideoConference $conference): string
    {
        return 'conference.guest_identity.'.$conference->id;
    }
}
