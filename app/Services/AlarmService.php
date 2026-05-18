<?php

namespace App\Services;

use App\Models\AlarmLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AlarmService
{
    /**
     * Scope alarm ke testing room yang boleh dilihat user.
     *
     * @param  array<string, mixed>  $filters
     */
    public function paginateForUser(User $user, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $q = AlarmLog::query()->with(['testingRoom', 'controlRoom'])->orderByDesc('triggered_at');

        $this->scopeAccessibleRooms($q, $user);

        if (! empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }
        if (! empty($filters['test_cell_id'])) {
            $q->where('testing_room_id', $filters['test_cell_id']);
        }
        if (! empty($filters['severity'])) {
            $q->where('severity', $filters['severity']);
        }

        return $q->paginate($perPage);
    }

    public function countActiveForUser(User $user): int
    {
        $q = AlarmLog::query()->where('status', 'active');
        $this->scopeAccessibleRooms($q, $user);

        return $q->count();
    }

    public function userMayAccessAlarm(User $user, AlarmLog $alarm): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->testing_room_id !== null) {
            return (int) $alarm->testing_room_id === (int) $user->testing_room_id;
        }

        if (! $user->control_room_id) {
            return false;
        }

        return (int) $alarm->control_room_id === (int) $user->control_room_id;
    }

    private function scopeAccessibleRooms(Builder $q, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->testing_room_id) {
            $q->where('testing_room_id', $user->testing_room_id);
        } elseif ($user->control_room_id) {
            $q->where('control_room_id', $user->control_room_id);
        } else {
            $q->whereRaw('1 = 0');
        }
    }
}
