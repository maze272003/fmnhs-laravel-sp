<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait for eager loading relationships to prevent N+1 queries.
 *
 * Usage in controllers:
 *   $students = Student::with($this->getEagerLoads(Student::class))->get();
 *
 * Or in models:
 *   Model::withRelations()->get();
 */
trait EagerLoadsRelations
{
    /**
     * Get the default eager load relations for a model.
     *
     * @param string $modelClass
     * @return array
     */
    protected function getEagerLoads(string $modelClass): array
    {
        return match ($modelClass) {
            \App\Models\Student::class => ['section', 'grades.subject', 'attendances'],
            \App\Models\Teacher::class => ['subjects', 'sections'],
            \App\Models\Grade::class => ['student', 'subject', 'teacher'],
            \App\Models\Attendance::class => ['student', 'subject'],
            \App\Models\Assignment::class => ['teacher', 'subject', 'section', 'submissions'],
            \App\Models\Submission::class => ['assignment', 'student'],
            \App\Models\Announcement::class => ['creator'],
            \App\Models\Schedule::class => ['section', 'subject', 'teacher', 'roomModel'],
            \App\Models\VideoConference::class => ['teacher', 'section', 'participants'],
            \App\Models\ProgressReport::class => ['student', 'teacher'],
            \App\Models\Portfolio::class => ['student', 'items'],
            \App\Models\Section::class => ['teacher', 'students'],
            \App\Models\LessonPlan::class => ['teacher', 'subject', 'section'],
            default => [],
        };
    }

    /**
     * Apply eager loading to a query builder.
     *
     * @param Builder $query
     * @param string $modelClass
     * @param array $additionalRelations
     * @return Builder
     */
    protected function withEagerLoads(Builder $query, string $modelClass, array $additionalRelations = []): Builder
    {
        $relations = array_merge(
            $this->getEagerLoads($modelClass),
            $additionalRelations
        );

        return $query->with($relations);
    }

    /**
     * Apply conditional eager loading based on request parameters.
     *
     * @param Builder $query
     * @param string $modelClass
     * @param array $requestedRelations
     * @return Builder
     */
    protected function withRequestedRelations(Builder $query, string $modelClass, array $requestedRelations = []): Builder
    {
        $allowedRelations = $this->getEagerLoads($modelClass);

        // Filter requested relations to only allowed ones
        $relations = array_intersect($requestedRelations, $allowedRelations);

        // If no specific relations requested, load all defaults
        if (empty($relations)) {
            $relations = $allowedRelations;
        }

        return $query->with($relations);
    }

    /**
     * Count related models without loading them.
     *
     * @param Builder $query
     * @param array $countRelations
     * @return Builder
     */
    protected function withRelationCounts(Builder $query, array $countRelations): Builder
    {
        return $query->withCount($countRelations);
    }
}
