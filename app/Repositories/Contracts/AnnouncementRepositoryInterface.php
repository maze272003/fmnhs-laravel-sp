<?php

namespace App\Repositories\Contracts;

use App\Models\Announcement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AnnouncementRepositoryInterface
{
    public function paginateLatest(int $perPage = 5): LengthAwarePaginator;

    public function latest(int $limit = 5): Collection;

    public function create(array $data): Announcement;

    public function findOrFail(int $id): Announcement;

    public function delete(Announcement $announcement): void;
}
