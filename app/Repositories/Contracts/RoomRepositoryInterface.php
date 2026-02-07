<?php

namespace App\Repositories\Contracts;

use App\Models\Room;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RoomRepositoryInterface
{
    public function paginateForAdmin(?string $search, int $perPage = 10): LengthAwarePaginator;

    public function create(array $data): Room;

    public function findOrFail(int $id): Room;

    public function update(Room $room, array $data): Room;

    public function delete(Room $room): void;

    public function hasSchedules(Room $room): bool;
}
