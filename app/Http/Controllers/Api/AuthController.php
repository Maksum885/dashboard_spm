<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'              => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'role'            => $user->role,
                'control_room_id' => $user->control_room_id,
            ],
            // Kirim info room yang bisa diakses
            'accessible_rooms' => $this->getAccessibleRooms($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('controlRoom');
        return response()->json([
            'user'             => $user,
            'accessible_rooms' => $this->getAccessibleRooms($user),
        ]);
    }

    private function getAccessibleRooms($user): array
    {
        if ($user->role === 'admin') {
            return \App\Models\TestingRoom::with('controlRoom')
                ->where('is_active', true)
                ->get()
                ->toArray();
        }

        return \App\Models\TestingRoom::with('controlRoom')
            ->where('control_room_id', $user->control_room_id)
            ->where('is_active', true)
            ->get()
            ->toArray();
    }
}
