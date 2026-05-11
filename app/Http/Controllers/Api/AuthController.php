<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TestingRoom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::with(['controlRoom', 'testingRoom'])
            ->where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $user->update(['last_login_at' => now()]);
        $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userResource($user),
            'accessible_rooms' => $this->getAccessibleRooms($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['controlRoom', 'testingRoom']);

        return response()->json([
            'user' => $this->userResource($user),
            'accessible_rooms' => $this->getAccessibleRooms($user),
        ]);
    }

    private function userResource(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'control_room_id' => $user->control_room_id,
            'control_room' => $user->controlRoom?->only(['id', 'name', 'code']),
            'last_login_at' => $user->last_login_at?->toISOString(),
        ];
    }

    private function getAccessibleRooms(User $user): array
    {
        $query = TestingRoom::with('controlRoom')->where('is_active', true);

        if ($user->role !== 'admin') {
            if ($user->testing_room_id) {
                $query->where('id', $user->testing_room_id);
            } elseif ($user->control_room_id) {
                $query->where('control_room_id', $user->control_room_id);
            } else {
                return [];
            }
        }

        return $query->get()->map(fn ($testRoom) => [
            'id' => $testRoom->id,
            'name' => $testRoom->name,
            'code' => $testRoom->code,
            'type' => $testRoom->type,
            'control_room' => $testRoom->controlRoom?->only(['id', 'name', 'code']) ?? [],
        ])->toArray();
    }
}
