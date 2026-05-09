<?php

namespace App\Services;

use App\Models\PlcRegisterLog;
use App\Models\PlcDevice;
use App\Models\TestingRoom;
use Illuminate\Support\Collection;

class ActivityLogService
{
    /**
     * Simpan satu entri log perubahan register
     */
    public function logChange(
        PlcDevice   $plcDevice,
        TestingRoom $testingRoom,
        array       $change,
        string      $occurredAt
    ): PlcRegisterLog {
        $address = $change['register_address'];
        $newVal  = $change['new_value'];
        $oldVal  = $change['old_value'];

        $changeType = $this->resolveChangeType($address, $newVal);

        return PlcRegisterLog::create([
            'plc_device_id'        => $plcDevice->id,
            'testing_room_id'      => $testingRoom->id,
            'control_room_id'      => $testingRoom->control_room_id,
            'register_address'     => $address,
            'register_name'        => $change['register_name'],
            'register_description' => $change['register_desc'] ?? $change['register_name'],
            'old_value'            => is_bool($oldVal) ? (int) $oldVal : $oldVal,
            'new_value'            => is_bool($newVal) ? (int) $newVal : $newVal,
            'decoded_value'        => $this->decodeValue($newVal),
            'change_type'          => $changeType,
            'occurred_at'          => $occurredAt,
        ]);
    }

    /**
     * Simpan banyak perubahan sekaligus (dari satu polling)
     */
    public function logBatch(
        PlcDevice   $plcDevice,
        TestingRoom $testingRoom,
        array       $changes,
        string      $occurredAt
    ): void {
        foreach ($changes as $change) {
            $this->logChange($plcDevice, $testingRoom, $change, $occurredAt);
        }
    }

    /**
     * Ambil log per testing room dengan filter
     */
    public function getLogsForRoom(
        int    $testingRoomId,
        array  $filters = [],
        int    $perPage = 50
    ): \Illuminate\Pagination\LengthAwarePaginator {
        $query = PlcRegisterLog::with(['plcDevice', 'testingRoom'])
            ->where('testing_room_id', $testingRoomId)
            ->orderByDesc('occurred_at');

        if (!empty($filters['register_address'])) {
            $query->where('register_address', $filters['register_address']);
        }
        if (!empty($filters['change_type'])) {
            $query->where('change_type', $filters['change_type']);
        }
        if (!empty($filters['from'])) {
            $query->where('occurred_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->where('occurred_at', '<=', $filters['to']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Ambil ringkasan aktivitas: berapa kali tiap jenis event terjadi
     */
    public function getSummaryForRoom(int $testingRoomId, int $lastHours = 24): array
    {
        $since = now()->subHours($lastHours);

        return PlcRegisterLog::where('testing_room_id', $testingRoomId)
            ->where('occurred_at', '>=', $since)
            ->selectRaw('change_type, COUNT(*) as total')
            ->groupBy('change_type')
            ->pluck('total', 'change_type')
            ->toArray();
    }

    // ────────────────────────────────────────────────────────────────────────────

    private function resolveChangeType(int $address, mixed $newVal): string
    {
        return match ($address) {
            40002   => $newVal ? 'alarm_triggered' : 'alarm_cleared',
            40003   => $newVal ? 'maintenance_on'  : 'maintenance_off',
            40006,
            40007   => 'roof_moving',
            40008   => 'door_locked',
            40009   => $newVal ? 'testing_started' : 'testing_stopped',
            default => is_bool($newVal) ? 'status_change' : 'value_change',
        };
    }

    private function decodeValue(mixed $value): string
    {
        if (is_bool($value)) return $value ? 'true' : 'false';
        if (is_float($value)) return number_format($value, 3);
        return (string) $value;
    }
}
