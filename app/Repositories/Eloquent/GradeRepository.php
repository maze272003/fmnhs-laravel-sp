<?php

namespace App\Repositories\Eloquent;

use App\Models\Grade;
use App\Repositories\Contracts\GradeRepositoryInterface;
use Illuminate\Support\Collection;

class GradeRepository implements GradeRepositoryInterface
{
    public function getByAdminLockingFilter(
        int $subjectId,
        int $sectionId,
        int $schoolYearId,
        bool $isLocked
    ): Collection {
        return Grade::where('subject_id', $subjectId)
            ->where('school_year_id', $schoolYearId)
            ->whereHas('student', fn ($q) => $q->where('section_id', $sectionId))
            ->where('is_locked', $isLocked)
            ->get();
    }

    public function updateLockState(Grade $grade, bool $isLocked, ?string $lockedBy): Grade
    {
        $grade->update([
            'is_locked' => $isLocked,
            'locked_at' => $isLocked ? now() : null,
            'locked_by' => $isLocked ? $lockedBy : null,
        ]);

        return $grade->fresh();
    }
}
