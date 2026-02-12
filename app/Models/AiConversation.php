<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type', 'user_id', 'subject', 'context', 'status',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class, 'conversation_id');
    }
}
