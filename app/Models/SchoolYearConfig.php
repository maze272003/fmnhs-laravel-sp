<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolYearConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_year',
        'is_active',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the currently active school year config.
     */
    public static function active(): ?self
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Set this school year as the active one (deactivate others).
     */
    public function activate(): void
    {
        self::where('is_active', true)->update(['is_active' => false]);
        $this->update(['is_active' => true, 'status' => 'active']);
    }

    /**
     * Close this school year.
     */
    public function close(): void
    {
        $this->update(['is_active' => false, 'status' => 'closed']);
    }
}
