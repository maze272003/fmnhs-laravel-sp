<?php

namespace App\Contracts\Repositories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Collection;

interface AnnouncementRepositoryInterface extends BaseRepositoryInterface
{
    public function getLatest(int $limit = 3): Collection;

    public function getByRole(string $role): Collection;

    public function search(string $query): Collection;

    public function getByAuthor(string $authorName): Collection;
}
