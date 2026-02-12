<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seat extends Model
{
    use HasFactory;

    protected $fillable = [
        'seating_arrangement_id', 'student_id', 'row', 'column', 'label',
    ];

    protected $casts = [
        'row' => 'integer',
        'column' => 'integer',
    ];

    public function seatingArrangement(): BelongsTo
    {
        return $this->belongsTo(SeatingArrangement::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
