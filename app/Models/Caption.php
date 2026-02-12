<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Caption extends Model
{
    use HasFactory;

    protected $fillable = [
        'conference_id', 'text', 'language', 'speaker_type', 'speaker_id', 'timestamp_ms',
    ];

    protected $casts = [
        'timestamp_ms' => 'integer',
    ];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(VideoConference::class, 'conference_id');
    }

    public function speaker(): MorphTo
    {
        return $this->morphTo();
    }
}
