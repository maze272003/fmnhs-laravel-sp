<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'conference_id', 'summary', 'key_points', 'action_items',
        'transcript', 'generated_by',
    ];

    protected $casts = [
        'key_points' => 'array',
        'action_items' => 'array',
    ];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(VideoConference::class, 'conference_id');
    }
}
