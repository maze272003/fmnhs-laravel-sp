<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConferenceRecording extends Model
{
    use HasFactory;

    protected $fillable = [
        'conference_id', 'title', 'disk', 'file_path', 'file_name', 'mime_type',
        'file_size', 'duration_seconds', 'type', 'status', 'chapters',
        'transcript_path', 'restricted', 'ready_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'duration_seconds' => 'integer',
        'restricted' => 'boolean',
        'chapters' => 'array',
        'ready_at' => 'datetime',
    ];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(VideoConference::class, 'conference_id');
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function getDownloadUrl(): ?string
    {
        if (! $this->isReady()) {
            return null;
        }

        try {
            return \Illuminate\Support\Facades\Storage::disk($this->disk)
                ->temporaryUrl($this->file_path, now()->addHour());
        } catch (\RuntimeException) {
            // Fallback for disks that don't support temporary URLs (e.g. local)
            return \Illuminate\Support\Facades\Storage::disk($this->disk)->url($this->file_path);
        }
    }
}
