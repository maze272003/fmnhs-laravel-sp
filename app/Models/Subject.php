<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    // ITO ANG KULANG: Ang relationship papunta sa Grades table
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}