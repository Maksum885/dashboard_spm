<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(
            User::query()->with(['controlRoom', 'testingRoom'])->orderBy('name')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['admin', 'operator'])],
            'control_room_id' => 'nullable|exists:control_rooms,id',
            'is_active' => 'boolean',
        ]);

        if ($data['role'] === 'operator' && empty($data['control_room_id'])) {
            return response()->json([
                'message' => 'Operator wajib memiliki control_room_id.',
            ], 422);
        }

        if ($data['role'] === 'admin') {
            $data['control_room_id'] = null;
        }

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json($user->load(['controlRoom', 'testingRoom']), 201);
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => ['sometimes', Rule::in(['admin', 'operator'])],
            'control_room_id' => 'nullable|exists:control_rooms,id',
            'testing_room_id' => 'nullable|exists:testing_rooms,id',
            'is_active' => 'boolean',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $role = $data['role'] ?? $user->role;
        $crId = array_key_exists('control_room_id', $data) ? $data['control_room_id'] : $user->control_room_id;
        $trId = array_key_exists('testing_room_id', $data) ? $data['testing_room_id'] : $user->testing_room_id;

        if ($role === 'operator' && ! $crId && ! $trId) {
            return response()->json([
                'message' => 'Operator wajib punya testing_room_id atau control_room_id.',
            ], 422);
        }

        if ($role === 'admin') {
            $data['control_room_id'] = null;
            $data['testing_room_id'] = null;
        }

        $user->update($data);

        return response()->json($user->fresh()->load(['controlRoom', 'testingRoom']));
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'User dihapus.']);
    }

    public function toggle(int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => ! $user->is_active]);

        return response()->json($user->fresh());
    }
}
