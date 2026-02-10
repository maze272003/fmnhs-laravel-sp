<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConferenceEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'conference_id', 'actor_id', 'event_type', 'metadata', 'conference_elapsed_seconds',
    ];

    protected $casts = [
        'metadata' => 'array',
        'conference_elapsed_seconds' => 'integer',
    ];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(VideoConference::class, 'conference_id');
    }
}
