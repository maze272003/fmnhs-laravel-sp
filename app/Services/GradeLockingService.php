<?php

namespace App\Services;

use App\Models\AuditTrail;
use App\Repositories\Contracts\GradeRepositoryInterface;
use Illuminate\Support\Facades\DB;

class GradeLockingService
{
    public function __construct(private readonly GradeRepositoryInterface $grades)
    {
    }

    public function lock(int $subjectId, int $sectionId, int $schoolYearId, ?object $adminUser = null): int
    {
        $targets = $this->grades->getByAdminLockingFilter($subjectId, $sectionId, $schoolYearId, false);

        DB::transaction(function () use ($targets, $adminUser) {
            foreach ($targets as $grade) {
                $this->grades->updateLockState($grade, true, $adminUser?->name ?? 'Admin');

                AuditTrail::log(
                    'Grade',
                    $grade->id,
                    'locked',
                    'is_locked',
                    'false',
                    'true',
                    'admin',
                    $adminUser?->id,
                    $adminUser?->name ?? 'Admin'
                );
            }
        });

        return $targets->count();
    }

    public function unlock(
        int $subjectId,
        int $sectionId,
        int $schoolYearId,
        string $reason,
        ?object $adminUser = null
    ): int {
        $targets = $this->grades->getByAdminLockingFilter($subjectId, $sectionId, $schoolYearId, true);

        DB::transaction(function () use ($targets, $reason, $adminUser) {
            foreach ($targets as $grade) {
                $this->grades->updateLockState($grade, false, null);

                AuditTrail::log(
                    'Grade',
                    $grade->id,
                    'unlocked',
                    'is_locked',
                    'true',
                    'false (Reason: ' . $reason . ')',
                    'admin',
                    $adminUser?->id,
                    $adminUser?->name ?? 'Admin'
                );
            }
        });

        return $targets->count();
    }
}
