<?php

// ============================================================
// Menerima webhook dari Python + expose ke frontend
// ============================================================
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlcDevice;
use App\Models\PlcRegisterLog;
use App\Models\PlcSnapshot;
use App\Models\AlarmLog;
use App\Models\TestingRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PlcController extends Controller
{
    /**
     * Webhook dari Python microservice
     * POST /api/plc/webhook
     */
    public function webhook(Request $request)
    {
        $data    = $request->json()->all();
        $roomId  = $data['room_id'];
        $changes = $data['changes'] ?? [];
        $snapshot= $data['snapshot'] ?? [];
        $status  = $data['status'];
        $time    = $data['polled_at'];

        // Cari testing room & plc device
        $testingRoom  = TestingRoom::find($roomId);
        $plcDevice = PlcDevice::where('testing_room_id', $roomId)->first();

        if (!$testingRoom || !$plcDevice) {
            return response()->json(['ok' => false, 'reason' => 'Room not found'], 404);
        }

        // Update status PLC
        $plcDevice->update([
            'status'       => $status === 'success' ? 'online' : $status,
            'last_seen_at' => now(),
        ]);

        // Simpan snapshot ke cache (untuk frontend realtime)
        Cache::put("plc_snapshot_{$roomId}", [
            'data'       => $snapshot,
            'status'     => $status,
            'polled_at'  => $time,
        ], 60);

        // Simpan snapshot ke database
        PlcSnapshot::create([
            'plc_device_id'   => $plcDevice->id,
            'testing_room_id' => $roomId,
            'register_values' => $snapshot,
            'poll_status'     => $status,
            'polled_at'       => $time,
        ]);

        // Simpan setiap perubahan register ke log
        foreach ($changes as $change) {
            $this->saveRegisterLog($plcDevice, $testingRoom, $change, $time);
        }

        // Broadcast ke frontend via Pusher (realtime)
        // broadcast(new PlcDataUpdated($roomId, $snapshot, $changes));

        return response()->json(['ok' => true]);
    }

    private function saveRegisterLog($plcDevice, $testingRoom, array $change, string $time): void
    {
        $address   = $change['register_address'];
        $newVal    = $change['new_value'];
        $oldVal    = $change['old_value'];

        // Tentukan change_type spesifik berdasarkan register
        $changeType = match($address) {
            40002 => $newVal ? 'alarm_triggered' : 'alarm_cleared',
            40003 => $newVal ? 'maintenance_on'  : 'maintenance_off',
            40006,
            40007 => 'roof_moving',
            40008 => 'door_locked',
            40009 => $newVal ? 'testing_started' : 'testing_stopped',
            default => is_bool($newVal) ? 'status_change' : 'value_change',
        };

        PlcRegisterLog::create([
            'plc_device_id'        => $plcDevice->id,
            'testing_room_id'      => $testingRoom->id,
            'control_room_id'      => $testingRoom->control_room_id,
            'register_address'     => $address,
            'register_name'        => $change['register_name'],
            'register_description' => $change['register_desc'],
            'old_value'            => is_bool($oldVal) ? (int)$oldVal : $oldVal,
            'new_value'            => is_bool($newVal) ? (int)$newVal : $newVal,
            'decoded_value'        => is_bool($newVal) ? ($newVal ? 'true' : 'false') : (string)$newVal,
            'change_type'          => $changeType,
            'occurred_at'          => $time,
        ]);

        // Jika alarm, simpan juga ke alarm_logs
        if ($address === 40002 && $newVal) {
            AlarmLog::create([
                'plc_device_id'   => $plcDevice->id,
                'testing_room_id' => $testingRoom->id,
                'control_room_id' => $testingRoom->control_room_id,
                'alarm_code'      => 'ALARM_ALERT',
                'alarm_description'=> 'Alarm Alert dari PLC',
                'severity'        => 'warning',
                'status'          => 'active',
                'triggered_at'    => $time,
            ]);
        }
    }

    /**
     * Ambil data realtime dari cache (untuk frontend polling)
     * GET /api/plc/{room_id}/data
     */
    public function getRoomData(int $room_id)
    {
        $cached = Cache::get("plc_snapshot_{$room_id}");
        return response()->json($cached ?? ['status' => 'no_data']);
    }

    /**
     * GET /api/plc/{room_id}/logs
     * History log perubahan register, bisa filter per register atau per type
     */
    public function getRoomLogs(Request $request, int $room_id)
    {
        $query = PlcRegisterLog::where('testing_room_id', $room_id)
            ->orderByDesc('occurred_at');

        if ($request->has('register_address')) {
            $query->where('register_address', $request->register_address);
        }
        if ($request->has('change_type')) {
            $query->where('change_type', $request->change_type);
        }
        if ($request->has('from')) {
            $query->where('occurred_at', '>=', $request->from);
        }

        return response()->json($query->paginate(50));
    }
}