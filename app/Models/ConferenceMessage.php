<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConferenceMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'conference_id', 'actor_id', 'display_name', 'role', 'content', 'type',
        'file_path', 'file_name', 'file_mime', 'file_size', 'conference_elapsed_seconds',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'conference_elapsed_seconds' => 'integer',
    ];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(VideoConference::class, 'conference_id');
    }

    public function isFile(): bool
    {
        return $this->type === 'file' && $this->file_path !== null;
    }

    public function isSystem(): bool
    {
        return $this->type === 'system';
    }
}
