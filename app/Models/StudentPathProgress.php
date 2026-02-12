<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPathProgress extends Model
{
    use HasFactory;

    protected $table = 'student_path_progress';

    protected $fillable = [
        'student_id', 'learning_path_id', 'current_node_id',
        'completed_nodes', 'score', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'completed_nodes' => 'array',
        'score' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function learningPath(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class);
    }

    public function currentNode(): BelongsTo
    {
        return $this->belongsTo(PathNode::class, 'current_node_id');
    }
}
