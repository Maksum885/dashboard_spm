<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\TestingRoom;

class RoomAccessMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user   = $request->user();
        $roomId = $request->route('room_id') ?? $request->route('testing_room_id');

        // Admin bisa akses semua
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Operator hanya bisa akses room di control room-nya
        if ($user->role === 'operator' && $roomId) {
            $testingRoom = TestingRoom::find($roomId);
            if ($testingRoom && $testingRoom->control_room_id === $user->control_room_id) {
                return $next($request);
            }
            return response()->json(['message' => 'Akses ditolak untuk room ini'], 403);
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }
}
