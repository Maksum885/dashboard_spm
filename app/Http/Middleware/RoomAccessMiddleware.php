<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

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

        // Operator: satu testing room, atau semua room di control room yang sama
        if ($user->role === 'operator' && $roomId) {
            if ($user->canAccessTestingRoomId((int) $roomId)) {
                return $next($request);
            }

            return response()->json(['message' => 'Akses ditolak untuk room ini'], 403);
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }
}
