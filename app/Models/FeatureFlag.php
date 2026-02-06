<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature',
        'enabled',
        'school_year',
        'description',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * Check if a feature is enabled (optionally for a school year).
     */
    public static function isEnabled(string $feature, ?string $schoolYear = null): bool
    {
        $query = self::where('feature', $feature);

        if ($schoolYear) {
            $query->where(function ($q) use ($schoolYear) {
                $q->where('school_year', $schoolYear)
                  ->orWhereNull('school_year');
            });
        }

        $flag = $query->first();

        return $flag ? $flag->enabled : true; // Default to enabled if no flag exists
    }
}
