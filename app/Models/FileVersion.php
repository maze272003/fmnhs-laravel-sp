<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FileVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_file_path', 'version_number', 'file_path', 'file_size',
        'uploaded_by_type', 'uploaded_by_id', 'notes',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'file_size' => 'integer',
    ];

    public function uploader(): MorphTo
    {
        return $this->morphTo('uploaded_by');
    }
}
