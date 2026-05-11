<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlcSnapshot extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'plc_device_id',
        'testing_room_id',
        'register_values',
        'poll_status',
        'error_message',
        'polled_at',
    ];

    protected function casts(): array
    {
        return [
            'register_values' => 'array',
            'polled_at' => 'datetime',
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
}
