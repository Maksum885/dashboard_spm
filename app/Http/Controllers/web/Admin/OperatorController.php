<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ControlRoom;
use App\Models\TestingRoom;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OperatorController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with(['controlRoom', 'testingRoom'])
            ->orderBy('name')
            ->get();

        return view('admin.operators.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        return view('admin.operators.form', [
            'user' => new User,
            'controlRooms' => $this->controlRoomsForForm(),
            'testingRooms' => $this->testingRoomsForForm(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        User::create($this->validated($request, null));

        return redirect()
            ->route('admin.operators.index')
            ->with('status', 'User added successfully.');
    }

    public function edit(User $user): View
    {
        return view('admin.operators.form', [
            'user' => $user->load(['controlRoom', 'testingRoom']),
            'controlRooms' => $this->controlRoomsForForm(),
            'testingRooms' => $this->testingRoomsForForm(),
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $user->update($this->validated($request, $user));

        return redirect()
            ->route('admin.operators.index')
            ->with('status', 'User updated.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => $data['password']]);

        return redirect()
            ->route('admin.operators.edit', $user)
            ->with('status', 'Password reset successfully.');
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        $state = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('status', "Account {$state}.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $user->tokens()->delete();
        $user->delete();

        return redirect()
            ->route('admin.operators.index')
            ->with('status', 'User deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?User $existing): array
    {
        $userId = $existing?->id;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'role' => ['required', Rule::in(['admin', 'operator', 'viewer'])],
            'control_room_id' => ['nullable', 'exists:control_rooms,id'],
            'testing_room_id' => ['nullable', 'exists:testing_rooms,id'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        if (! $existing) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $data = $request->validate($rules);

        $data['is_active'] = $request->boolean('is_active', $existing?->is_active ?? true);

        $role = $data['role'];
        $crId = $data['control_room_id'] ?? null;
        $trId = $data['testing_room_id'] ?? null;

        if (in_array($role, ['operator', 'viewer'], true) && ! $crId && ! $trId) {
            throw ValidationException::withMessages([
                'testing_room_id' => 'Select a testing room or control room for this role.',
            ]);
        }

        if ($role === 'admin') {
            $data['control_room_id'] = null;
            $data['testing_room_id'] = null;
        }

        return $data;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, ControlRoom>
     */
    private function controlRoomsForForm()
    {
        return ControlRoom::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, TestingRoom>
     */
    private function testingRoomsForForm()
    {
        return TestingRoom::query()
            ->where('is_active', true)
            ->with('controlRoom')
            ->orderBy('control_room_id')
            ->orderBy('code')
            ->get();
    }
}
