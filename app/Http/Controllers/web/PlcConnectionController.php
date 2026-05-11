<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PlcDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlcConnectionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if ($user->role === 'viewer') {
            abort(403, 'Viewers cannot open settings.');
        }

        $q = PlcDevice::query()
            ->with(['testingRoom.controlRoom'])
            ->orderBy('testing_room_id');

        if (! $user->isAdmin()) {
            if ($user->testing_room_id) {
                $q->where('testing_room_id', $user->testing_room_id);
            } else {
                $q->whereHas(
                    'testingRoom',
                    fn ($q) => $q->where('control_room_id', $user->control_room_id)
                );
            }
        }

        $devices = $q->get();

        $devicesByKind = $devices->groupBy(function (PlcDevice $d) {
            return match ($d->testingRoom?->type) {
                'pit' => 'pit',
                'cell' => 'cell',
                default => 'other',
            };
        });

        $sortNamed = static fn ($collection) => $collection
            ->sortBy(fn (PlcDevice $d) => strtolower($d->testingRoom->name ?? ''))
            ->values();

        $devicesByKind = [
            'pit' => $sortNamed($devicesByKind->get('pit', collect())),
            'cell' => $sortNamed($devicesByKind->get('cell', collect())),
            'other' => $sortNamed($devicesByKind->get('other', collect())),
        ];

        $highlightRoomId = $request->query('testing_room_id');

        return view('settings.plc-connection', [
            'devices' => $devices,
            'devicesByKind' => $devicesByKind,
            'highlightRoomId' => $highlightRoomId !== null && $highlightRoomId !== ''
                ? (int) $highlightRoomId
                : null,
        ]);
    }

    public function update(Request $request, PlcDevice $plc_device): RedirectResponse
    {
        $user = $request->user();
        if ($user->role === 'viewer') {
            abort(403);
        }

        $plc_device->loadMissing('testingRoom');

        if (! $user->isAdmin()) {
            if ($user->testing_room_id
                && (int) $plc_device->testing_room_id !== (int) $user->testing_room_id) {
                abort(403);
            }
            if (! $user->testing_room_id
                && (int) $plc_device->testingRoom->control_room_id !== (int) $user->control_room_id) {
                abort(403);
            }
        }

        $data = $request->validate([
            'ip_address' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'unit_id' => 'required|integer|min:0|max:255',
            'is_enabled' => 'sometimes|boolean',
        ]);

        $data['is_enabled'] = $request->boolean('is_enabled');
        $data['name'] = 'PLC '.($plc_device->testingRoom->name ?? 'device');

        $plc_device->update($data);

        return back()->with(
            'status',
            'Saved. Only rooms with “Enable polling” checked are read by the Modbus bridge (refreshes about every 15s; or restart uvicorn).'
        );
    }
}
