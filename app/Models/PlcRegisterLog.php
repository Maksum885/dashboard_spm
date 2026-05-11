<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlcRegisterLog extends Model
{
    protected $fillable = [
        'plc_device_id',
        'testing_room_id',
        'control_room_id',
        'register_address',
        'register_name',
        'register_description',
        'old_value',
        'new_value',
        'decoded_value',
        'change_type',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }

    public function plcDevice(): BelongsTo
    {
        return $this->belongsTo(PlcDevice::class);
    }

    public function testingRoom(): BelongsTo
    {
        return $this->belongsTo(TestingRoom::class);
    }

    public function controlRoom(): BelongsTo
    {
        return $this->belongsTo(ControlRoom::class);
    }
}
