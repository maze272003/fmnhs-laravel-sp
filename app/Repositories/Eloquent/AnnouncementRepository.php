<?php

namespace App\Repositories\Eloquent;

use App\Models\Announcement;
use App\Repositories\Contracts\AnnouncementRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AnnouncementRepository implements AnnouncementRepositoryInterface
{
    public function paginateLatest(int $perPage = 5): LengthAwarePaginator
    {
        return Announcement::latest()->paginate($perPage);
    }

    public function latest(int $limit = 5): Collection
    {
        return Announcement::latest()->take($limit)->get();
    }

    public function create(array $data): Announcement
    {
        return Announcement::create($data);
    }

    public function findOrFail(int $id): Announcement
    {
        return Announcement::findOrFail($id);
    }

    public function delete(Announcement $announcement): void
    {
        $announcement->delete();
    }
}
