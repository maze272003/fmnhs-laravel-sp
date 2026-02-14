<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class VideoConference extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id', 'section_id', 'title', 'slug', 'is_active', 'started_at', 'ended_at',
        'terminated_at', 'visibility', 'secret_key_hash',
        'settings', 'branding_logo', 'branding_color', 'recording_enabled',
        'chat_enabled', 'screen_share_enabled', 'guest_access',
        'max_participants', 'password', 'notification_rules',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'terminated_at' => 'datetime',
        'settings' => 'array',
        'visibility' => 'string',
        'recording_enabled' => 'boolean',
        'chat_enabled' => 'boolean',
        'screen_share_enabled' => 'boolean',
        'guest_access' => 'boolean',
        'notification_rules' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConferenceParticipant::class, 'conference_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConferenceMessage::class, 'conference_id');
    }

    public function recordings(): HasMany
    {
        return $this->hasMany(ConferenceRecording::class, 'conference_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ConferenceEvent::class, 'conference_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(ConferenceNotification::class, 'conference_id');
    }

    public function canStudentJoin(Student $student): bool
    {
        if (! $this->is_active || $this->ended_at !== null) {
            return false;
        }

        if ($this->isPublicRoom()) {
            return true;
        }

        if (! $student->section_id) {
            return false;
        }

        if ($this->section_id !== null) {
            return (int) $student->section_id === (int) $this->section_id;
        }

        return Schedule::where('teacher_id', $this->teacher_id)
            ->where('section_id', $student->section_id)
            ->exists()
            || Section::where('teacher_id', $this->teacher_id)
                ->whereKey($student->section_id)
                ->exists();
    }

    public function isPublicRoom(): bool
    {
        return strtolower((string) ($this->visibility ?? 'private')) === 'public';
    }

    public function isPrivateRoom(): bool
    {
        return ! $this->isPublicRoom();
    }

    public function hasSecretKey(): bool
    {
        return is_string($this->secret_key_hash) && $this->secret_key_hash !== '';
    }

    public function requiresSecretKey(): bool
    {
        return $this->isPrivateRoom() && $this->hasSecretKey();
    }

    public function verifySecretKey(?string $key): bool
    {
        if (! $this->requiresSecretKey()) {
            return true;
        }

        if (! is_string($key) || trim($key) === '') {
            return false;
        }

        return Hash::check(trim($key), (string) $this->secret_key_hash);
    }

    public function getElapsedSeconds(): int
    {
        if (! $this->started_at) {
            return 0;
        }
        $end = $this->ended_at ?? now();

        return (int) $this->started_at->diffInSeconds($end);
    }

    public function getCurrentElapsedSeconds(): int
    {
        if (! $this->started_at) {
            return 0;
        }

        return (int) $this->started_at->diffInSeconds(now());
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings ?? [], $key, $default);
    }

    public function activeParticipantCount(): int
    {
        return $this->participants()
            ->whereNotNull('joined_at')
            ->whereNull('left_at')
            ->count();
    }
}
