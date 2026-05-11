<?php

namespace App\Services;

use App\Models\AlarmLog;
use App\Models\ControlRoom;
use App\Models\TestingRoom;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PlcDataService
{
    /**
     * Bentuk payload dashboard sama seperti normalizeDashboardPayload di frontend:
     * { controlRooms: Record<string, object>, utilities: object }
     */
    public function buildDashboardPayload(User $user): array
    {
        if (! $user->isAdmin() && $user->testing_room_id) {
            $tr = TestingRoom::query()
                ->where('is_active', true)
                ->with(['controlRoom', 'plcDevices'])
                ->find($user->testing_room_id);
            if (! $tr || ! $tr->controlRoom) {
                return ['controlRooms' => [], 'utilities' => $this->defaultUtilities()];
            }
            $cr = $tr->controlRoom;
            $cr->setRelation('testingRooms', collect([$tr]));
            $key = $this->controlRoomKey($cr->code);

            return [
                'controlRooms' => [$key => $this->buildControlRoomShape($cr)],
                'utilities' => $this->defaultUtilities(),
            ];
        }

        $crQuery = ControlRoom::query()->where('is_active', true)->orderBy('code');

        if (! $user->isAdmin()) {
            if (! $user->control_room_id) {
                return ['controlRooms' => [], 'utilities' => $this->defaultUtilities()];
            }
            $crQuery->where('id', $user->control_room_id);
        }

        $controlRooms = [];
        foreach ($crQuery->with([
            'testingRooms' => function ($q) {
                $q->where('is_active', true)->orderBy('code')->with('plcDevices');
            },
        ])->get() as $cr) {
            $key = $this->controlRoomKey($cr->code);
            $controlRooms[$key] = $this->buildControlRoomShape($cr);
        }

        return [
            'controlRooms' => $controlRooms,
            'utilities' => $this->defaultUtilities(),
        ];
    }

    private function controlRoomKey(string $code): string
    {
        $n = preg_replace('/\D/', '', $code) ?: '1';

        return 'cr'.$n;
    }

    private function buildControlRoomShape(ControlRoom $cr): array
    {
        $linePressure = 0.0;
        $doorLock = 'locked';

        $rooms = [];
        foreach ($cr->testingRooms as $tr) {
            $rooms[] = $this->buildTestingRoomShape($tr, $linePressure, $doorLock);
        }

        return [
            'tp' => 'CONTROL ROOM',
            'nm' => $cr->name,
            'zn' => $cr->description ?? $cr->name,
            'col' => '#1564c0',
            'line_pressure' => round(max($linePressure, 6.2), 2),
            'door_lock' => $doorLock,
            'st' => 'NORMAL',
            'events' => [
                ['t' => now()->format('H:i'), 'c' => 'ok', 'm' => 'System ready — API data'],
            ],
            'rooms' => $rooms,
        ];
    }

    private function buildTestingRoomShape(TestingRoom $tr, &$crLinePressure, &$crDoorLock): array
    {
        $snapshot = Cache::get('plc_snapshot_'.$tr->id);
        $registers = is_array($snapshot) ? ($snapshot['data'] ?? []) : [];

        $uiId = $this->testingRoomUiId($tr);
        $isPit = $tr->type === 'pit';

        $pressureBar = $this->registerFloat($registers, ['40011', 'pressure_line', 'pres_bbm']);
        if ($pressureBar > 0) {
            $crLinePressure = max($crLinePressure, $pressureBar);
        }

        $roof = $this->deriveRoofStateFromRegisters($registers);

        $maintenance = $this->registerBool($registers, ['40003', 'mode_maintenance']);
        $mode = $maintenance ? 'MAINTENANCE' : 'AUTO';

        $doorLocked = $this->registerBool($registers, ['40008', 'pintu_terkunci', 'door_locked']);
        $doorLockStr = $doorLocked ? 'locked' : 'unlocked';
        $crDoorLock = $doorLockStr;

        $testingOn = $this->registerBool($registers, ['40009', 'testing_dimulai']);
        $phase = $testingOn ? 'RUNNING' : 'STANDBY';

        $presIn = $this->registerPressureBar($registers, ['40001', 'tekanan_masuk']);
        $pres2 = $this->registerFloat($registers, ['40012', 'pressure_2']);

        $roofMovingOpen = $this->registerBool($registers, ['40006', 'roof_bergerak_buka']);
        $roofMovingClose = $this->registerBool($registers, ['40007', 'roof_bergerak_tutup']);
        $roofOpenBit = $this->registerBool($registers, ['40005', 'roof_terbuka']);
        $roofClosedBit = $this->registerBool($registers, ['40004', 'roof_tertutup']);

        $alarms = $this->activeAlarmFlags($tr->id);

        $events = $this->recentEventsForRoom($tr->id);

        $base = [
            'id' => $uiId,
            'api_room_id' => $tr->id,
            'tp' => $isPit ? 'TEST PIT' : 'TEST CELL',
            'nm' => $tr->name,
            'col' => $isPit ? '#c27a00' : '#059652',
            'st' => 'OPERATIONAL',
            'phase' => $phase,
            'roof_state' => $roof,
            'mode' => $mode,
            'door_lock' => $doorLockStr,
            'dual_motor' => true,
            'pres_in' => round($presIn, 2),
            'pres_2' => round($pres2, 2),
            'roof_moving_open' => $roofMovingOpen,
            'roof_moving_close' => $roofMovingClose,
            'reg_roof_open' => $roofOpenBit,
            'reg_roof_closed' => $roofClosedBit,
            'alarm_emergency' => $alarms['emergency'],
            'alarm_pressure_in' => $alarms['pressure_in'],
            'alarm_left_motor' => $alarms['left_motor'],
            'alarm_right_motor' => $alarms['right_motor'],
            'alarm_motor' => $alarms['motor'],
            'events' => $events,
        ];

        if ($isPit) {
            $tp = $this->registerFloat($registers, ['test_pressure', '40011']);
            $base += [
                'test_pressure' => $tp,
                'target_pressure' => 100.0,
                'pressure_rate' => 0,
                'max_st' => 'OFF',
                'fluid_type' => 'AIR BERSIH',
            ];
        } else {
            $base += [
                'pres_bbm' => $pressureBar > 0 ? round($pressureBar, 2) : 6.2,
            ];
        }

        $device = $tr->plcDevices->first();
        $base['plc_link_status'] = $device?->status ?? 'offline';
        $base['plc_last_seen_at'] = $device?->last_seen_at?->toIso8601String();

        return $base;
    }

    private function testingRoomUiId(TestingRoom $tr): string
    {
        preg_match('/(\d+)/', $tr->code, $m);
        $n = $m[1] ?? (string) $tr->id;

        return $tr->type === 'pit' ? 'pit'.$n : 'cell'.$n;
    }

    /**
     * @param  array<string, mixed>  $registers
     */
    /**
     * Tekanan masuk (#40001) sering dikirim sebagai uint16 ratusan (510 = 5.1 bar).
     */
    private function registerPressureBar(array $registers, array $keys): float
    {
        $v = $this->registerFloat($registers, $keys);

        return $v > 50 ? $v / 100.0 : $v;
    }

    /**
     * Roof: prioritas bit bergerak (#40006/#40007), lalu tertutup/terbuka (#40004/#40005).
     *
     * @param  array<string, mixed>  $registers
     */
    private function deriveRoofStateFromRegisters(array $registers): string
    {
        if ($this->registerBool($registers, ['40006', 'roof_bergerak_buka'])
            || $this->registerBool($registers, ['40007', 'roof_bergerak_tutup'])) {
            return 'STANDBY';
        }
        if ($this->registerBool($registers, ['40005', 'roof_terbuka'])) {
            return 'OPEN';
        }
        if ($this->registerBool($registers, ['40004', 'roof_tertutup'])) {
            return 'CLOSE';
        }

        return 'CLOSE';
    }

    private function registerFloat(array $registers, array $keys): float
    {
        foreach ($keys as $k) {
            $cell = $registers[$k] ?? null;
            if (is_array($cell) && isset($cell['value'])) {
                return (float) $cell['value'];
            }
        }

        return 0.0;
    }

    /**
     * @param  array<string, mixed>  $registers
     */
    private function registerBool(array $registers, array $keys): bool
    {
        foreach ($keys as $k) {
            $cell = $registers[$k] ?? null;
            if (is_array($cell) && array_key_exists('value', $cell)) {
                return (bool) $cell['value'];
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $registers
     */
    private function registerString(array $registers, array $keys): string
    {
        foreach ($keys as $k) {
            $cell = $registers[$k] ?? null;
            if (is_array($cell) && isset($cell['value'])) {
                return (string) $cell['value'];
            }
        }

        return '';
    }

    /**
     * @return array{emergency: bool, pressure_in: bool, left_motor: bool, right_motor: bool, motor: bool}
     */
    private function activeAlarmFlags(int $testingRoomId): array
    {
        $codes = AlarmLog::query()
            ->where('testing_room_id', $testingRoomId)
            ->where('status', 'active')
            ->pluck('alarm_code')
            ->map(fn ($c) => strtoupper((string) $c))
            ->all();

        $haystack = implode(' ', $codes);

        return [
            'emergency' => str_contains($haystack, 'EMERGENCY'),
            'pressure_in' => str_contains($haystack, 'PRESSURE'),
            'left_motor' => str_contains($haystack, 'LEFT'),
            'right_motor' => str_contains($haystack, 'RIGHT'),
            'motor' => str_contains($haystack, 'MOTOR'),
        ];
    }

    private function recentEventsForRoom(int $testingRoomId): array
    {
        $rows = \App\Models\PlcRegisterLog::query()
            ->where('testing_room_id', $testingRoomId)
            ->orderByDesc('occurred_at')
            ->limit(8)
            ->get(['occurred_at', 'change_type', 'register_description']);

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                't' => $row->occurred_at->format('H:i'),
                'c' => match ($row->change_type) {
                    'alarm_triggered' => 'cr',
                    'alarm_cleared' => 'ok',
                    default => 'in',
                },
                'm' => $row->register_description ?: $row->change_type,
            ];
        }

        return $out;
    }

    /**
     * @return array{pam: mixed, listrik: mixed, hvac: mixed}
     */
    private function defaultUtilities(): array
    {
        return [
            'pam' => [
                'pump1' => ['nm' => 'Pompa 1', 'st' => 'RUNNING', 'flow' => 125.4, 'pres' => 4.8, 'amp' => 18.2, 'temp_motor' => 62.4, 'run_hours' => 2840],
                'pump2' => ['nm' => 'Pompa 2', 'st' => 'STANDBY', 'flow' => 0, 'pres' => 0, 'amp' => 0, 'temp_motor' => 28.1, 'run_hours' => 1420],
                'tank_level' => 78,
                'total_flow_hari' => 4820,
                'last_service' => now()->format('Y-m-d'),
            ],
            'listrik' => [
                'total_kw' => 284.2,
                'pf' => 0.92,
                'freq' => 50.0,
                'volt_r' => 220.4,
                'volt_s' => 219.8,
                'volt_t' => 221.2,
                'amp_r' => 142.8,
                'amp_s' => 140.2,
                'amp_t' => 143.6,
                'kwh_hari' => 1842.4,
                'st' => 'NORMAL',
            ],
            'hvac' => [
                'unit1' => ['nm' => 'AC CR1', 'st' => 'RUNNING', 'set' => 22, 'actual' => 23.1, 'amp' => 8.4],
                'unit2' => ['nm' => 'AC CR2', 'st' => 'RUNNING', 'set' => 22, 'actual' => 23.8, 'amp' => 8.9],
                'unit3' => ['nm' => 'AC CR3', 'st' => 'RUNNING', 'set' => 22, 'actual' => 22.8, 'amp' => 8.1],
                'filter_st' => 'OK',
                'next_service' => now()->addMonths(2)->format('Y-m-d'),
            ],
        ];
    }
}
