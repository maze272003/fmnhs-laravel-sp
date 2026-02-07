<?php

namespace App\Repositories\Eloquent;

use App\Models\Subject;
use App\Repositories\Contracts\SubjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubjectRepository implements SubjectRepositoryInterface
{
    public function paginateForAdmin(bool $archived, int $perPage = 10): LengthAwarePaginator
    {
        return Subject::when($archived, fn ($query) => $query->onlyTrashed())
            ->paginate($perPage);
    }

    public function create(array $data): Subject
    {
        return Subject::create($data);
    }

    public function update(Subject $subject, array $data): Subject
    {
        $subject->update($data);
        return $subject->fresh();
    }

    public function archive(Subject $subject): void
    {
        $subject->delete();
    }

    public function restoreById(int $id): Subject
    {
        $subject = Subject::onlyTrashed()->findOrFail($id);
        $subject->restore();
        return $subject->fresh();
    }
}
