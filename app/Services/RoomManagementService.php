<?php

namespace App\Services;

use App\Models\AuditTrail;
use App\Models\Room;
use App\Repositories\Contracts\RoomRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class RoomManagementService
{
    public function __construct(private readonly RoomRepositoryInterface $rooms)
    {
    }

    public function list(?string $search): LengthAwarePaginator
    {
        return $this->rooms->paginateForAdmin($search, 10);
    }

    public function create(array $validated, ?object $adminUser = null): Room
    {
        $validated['is_available'] = true;
        $room = $this->rooms->create($validated);

        AuditTrail::log(
            'Room',
            $room->id,
            'created',
            null,
            null,
            $room->toArray(),
            'admin',
            $adminUser?->id,
            $adminUser?->name ?? 'Admin'
        );

        return $room;
    }

    public function update(int $id, array $validated): Room
    {
        $room = $this->rooms->findOrFail($id);
        return $this->rooms->update($room, $validated);
    }

    public function delete(int $id): void
    {
        $room = $this->rooms->findOrFail($id);
        if ($this->rooms->hasSchedules($room)) {
            throw ValidationException::withMessages([
                'error' => 'Cannot delete room with active schedules.',
            ]);
        }

        $this->rooms->delete($room);
    }
}
