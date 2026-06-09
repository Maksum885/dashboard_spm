<?php

namespace App\Http\Controllers\Web\Operator;

use App\Http\Controllers\Controller;
use App\Models\RoomCamera;
use App\Models\TestingRoom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CameraConnectionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if ($user->role === 'viewer') {
            abort(403, 'Viewers cannot open settings.');
        }

        $q = TestingRoom::query()
            ->with(['controlRoom', 'roomCameras'])
            ->where('is_active', true)
            ->orderBy('id');

        if (! $user->isAdmin()) {
            if ($user->testing_room_id) {
                $q->where('id', $user->testing_room_id);
            } else {
                $q->where('control_room_id', $user->control_room_id);
            }
        }

        $rooms = $q->get();

        $roomsByKind = $rooms->groupBy(fn (TestingRoom $r) => match ($r->type) {
            'pit' => 'pit',
            'cell' => 'cell',
            default => 'other',
        });

        $sortNamed = static fn ($collection) => $collection
            ->sortBy(fn (TestingRoom $r) => strtolower($r->name ?? ''))
            ->values();

        $roomsByKind = [
            'pit' => $sortNamed($roomsByKind->get('pit', collect())),
            'cell' => $sortNamed($roomsByKind->get('cell', collect())),
            'other' => $sortNamed($roomsByKind->get('other', collect())),
        ];

        $highlightRoomId = $request->query('testing_room_id');

        return view('operator.camera-connection', [
            'rooms' => $rooms,
            'roomsByKind' => $roomsByKind,
            'highlightRoomId' => $highlightRoomId !== null && $highlightRoomId !== ''
                ? (int) $highlightRoomId
                : null,
        ]);
    }

    public function update(Request $request, RoomCamera $room_camera): RedirectResponse
    {
        $user = $request->user();
        if ($user->role === 'viewer') {
            abort(403);
        }

        $room_camera->loadMissing('testingRoom');

        if (! $user->isAdmin()) {
            if ($user->testing_room_id
                && (int) $room_camera->testing_room_id !== (int) $user->testing_room_id) {
                abort(403);
            }
            if (! $user->testing_room_id
                && (int) $room_camera->testingRoom->control_room_id !== (int) $user->control_room_id) {
                abort(403);
            }
        }

        $data = $request->validate([
            'rtsp_host' => 'required|string|max:255',
            'rtsp_port' => 'required|integer|min:1|max:65535',
            'rtsp_path' => 'required|string|max:255',
            'rtsp_username' => 'nullable|string|max:128',
            'rtsp_password' => 'nullable|string|max:128',
            'is_enabled' => 'sometimes|boolean',
        ]);

        $data['is_enabled'] = $request->boolean('is_enabled');
        if ($data['rtsp_password'] === null || $data['rtsp_password'] === '') {
            unset($data['rtsp_password']);
        }

        $room_camera->update($data);

        return back()->with(
            'status',
            'Saved. Enabled cameras are picked up by the CV service within about 15 seconds.'
        );
    }
}
