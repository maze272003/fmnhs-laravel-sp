<?php

namespace App\Repositories\Eloquent;

use App\Models\Room;
use App\Repositories\Contracts\RoomRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoomRepository implements RoomRepositoryInterface
{
    public function paginateForAdmin(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return Room::query()
            ->when($search, function ($query, $searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere('building', 'LIKE', '%' . $searchTerm . '%');
                });
            })
            ->orderBy('building')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function create(array $data): Room
    {
        return Room::create($data);
    }

    public function findOrFail(int $id): Room
    {
        return Room::findOrFail($id);
    }

    public function update(Room $room, array $data): Room
    {
        $room->update($data);
        return $room->fresh();
    }

    public function delete(Room $room): void
    {
        $room->delete();
    }

    public function hasSchedules(Room $room): bool
    {
        return $room->schedules()->exists();
    }
}
