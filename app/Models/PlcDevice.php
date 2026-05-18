<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlcDevice extends Model
{
    protected $fillable = [
        'testing_room_id',
        'name',
        'ip_address',
        'port',
        'unit_id',
        'is_enabled',
        'status',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function testingRoom(): BelongsTo
    {
        return $this->belongsTo(TestingRoom::class);
    }

    public function plcSnapshots(): HasMany
    {
        return $this->hasMany(PlcSnapshot::class);
    }

    public function plcRegisterLogs(): HasMany
    {
        return $this->hasMany(PlcRegisterLog::class);
    }

    public function alarmLogs(): HasMany
    {
        return $this->hasMany(AlarmLog::class);
    }
}
