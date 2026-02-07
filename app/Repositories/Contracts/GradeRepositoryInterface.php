<?php

namespace App\Repositories\Contracts;

use App\Models\Grade;
use Illuminate\Support\Collection;

interface GradeRepositoryInterface
{
    public function getByAdminLockingFilter(
        int $subjectId,
        int $sectionId,
        int $schoolYearId,
        bool $isLocked
    ): Collection;

    public function updateLockState(Grade $grade, bool $isLocked, ?string $lockedBy): Grade;
}
