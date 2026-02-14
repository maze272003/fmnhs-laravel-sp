<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'content', 
        'author_name', 
        'role', 
        'image',
        'target_audience',
        'created_by_type',
        'created_by_id',
    ];

    /**
     * Get the user (admin, teacher, etc.) who created this announcement.
     */
    public function createdBy(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo('created_by');
    }
}