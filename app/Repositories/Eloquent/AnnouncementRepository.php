<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\AnnouncementRepositoryInterface;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\Collection;

class AnnouncementRepository extends BaseRepository implements AnnouncementRepositoryInterface
{
    public function __construct(Announcement $model)
    {
        parent::__construct($model);
    }

    public function getLatest(int $limit = 3): Collection
    {
        return $this->model->latest()->take($limit)->get();
    }

    public function getByRole(string $role): Collection
    {
        return $this->model->where('role', $role)
            ->latest()
            ->get();
    }

    public function search(string $query): Collection
    {
        return $this->model->where('title', 'like', "%{$query}%")
            ->orWhere('content', 'like', "%{$query}%")
            ->latest()
            ->get();
    }

    public function getByAuthor(string $authorName): Collection
    {
        return $this->model->where('author_name', $authorName)
            ->latest()
            ->get();
    }
}
