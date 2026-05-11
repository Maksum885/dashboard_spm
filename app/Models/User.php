<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'control_room_id',
        'testing_room_id',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function controlRoom(): BelongsTo
    {
        return $this->belongsTo(ControlRoom::class);
    }

    public function testingRoom(): BelongsTo
    {
        return $this->belongsTo(TestingRoom::class);
    }

    /**
     * Akses data/API untuk satu testing room (operator room-dedicated).
     */
    public function isScopedToSingleTestingRoom(): bool
    {
        return $this->testing_room_id !== null;
    }

    /**
     * Legacy: akses semua room dalam satu control room.
     */
    public function isScopedToControlRoomOnly(): bool
    {
        return $this->testing_room_id === null && $this->control_room_id !== null;
    }

    public function canAccessTestingRoomId(int $testingRoomId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        if ($this->testing_room_id !== null) {
            return (int) $this->testing_room_id === (int) $testingRoomId;
        }
        if ($this->control_room_id !== null) {
            $room = TestingRoom::query()->find($testingRoomId);

            return $room && (int) $room->control_room_id === (int) $this->control_room_id;
        }

        return false;
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->testing_room_id) {
                $tr = TestingRoom::query()->find($user->testing_room_id);
                $user->control_room_id = $tr?->control_room_id;
            }
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canMutateScada(): bool
    {
        return in_array($this->role, ['admin', 'operator'], true);
    }
}
