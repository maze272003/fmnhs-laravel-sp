<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    use HasFactory;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'action',
        'field',
        'old_value',
        'new_value',
        'user_type',
        'user_id',
        'user_name',
    ];

    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Log an audit trail entry.
     */
    public static function log(
        string $auditableType,
        int $auditableId,
        string $action,
        ?string $field = null,
        $oldValue = null,
        $newValue = null,
        ?string $userType = null,
        ?int $userId = null,
        ?string $userName = null
    ): self {
        return self::create([
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'action' => $action,
            'field' => $field,
            'old_value' => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
            'new_value' => is_array($newValue) ? json_encode($newValue) : $newValue,
            'user_type' => $userType,
            'user_id' => $userId,
            'user_name' => $userName,
        ]);
    }
}
