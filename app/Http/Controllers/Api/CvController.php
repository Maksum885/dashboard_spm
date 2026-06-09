<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoomCamera;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CvController extends Controller
{
    /**
     * GET /api/cv/bridge-cameras?token=...
     * Enabled RTSP cameras for the Python CV service (device_loader pattern).
     */
    public function bridgeCameras(Request $request): JsonResponse
    {
        $expected = (string) env('PLC_BRIDGE_TOKEN', '');
        if ($expected === '' || ! hash_equals($expected, (string) $request->query('token', ''))) {
            return response()->json(['ok' => false, 'reason' => 'Forbidden'], 403);
        }

        $rows = RoomCamera::query()
            ->with('testingRoom:id,name')
            ->where('is_enabled', true)
            ->orderBy('testing_room_id')
            ->orderBy('slot')
            ->get();

        $cameras = [];
        foreach ($rows as $cam) {
            $roomId = (string) $cam->testing_room_id;
            $slot = (string) $cam->slot;
            $cameras[$roomId][$slot] = [
                'rtsp_url' => $cam->buildRtspUrl(),
                'name' => $cam->name,
                'room_name' => $cam->testingRoom?->name ?? "Room {$roomId}",
            ];
        }

        return response()->json(['ok' => true, 'cameras' => $cameras]);
    }
}
