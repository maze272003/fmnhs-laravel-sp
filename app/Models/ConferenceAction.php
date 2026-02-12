<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConferenceAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'conference_id', 'student_id', 'action_type', 'points_earned', 'metadata',
    ];

    protected $casts = [
        'points_earned' => 'integer',
        'metadata' => 'array',
    ];

    public function conference(): BelongsTo
    {
        return $this->belongsTo(VideoConference::class, 'conference_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
