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

    /** English label for admin log UI; does not change stored data or PLC keys. */
    public function displayDescription(): string
    {
        $name = $this->register_name;
        if ($name !== null && $name !== '') {
            $byKey = config('plc_registers.labels', []);
            if (isset($byKey[$name])) {
                return $byKey[$name];
            }
        }

        $stored = $this->register_description;
        if ($stored !== null && $stored !== '') {
            $legacy = config('plc_registers.legacy_descriptions', []);
            if (isset($legacy[$stored])) {
                return $legacy[$stored];
            }

            return $stored;
        }

        return '';
    }

    /** Friendly register label for admin log (hides internal keys like roof_bergerak_buka). */
    public function displayRegisterLabel(): string
    {
        $label = $this->displayDescription();
        if ($label !== '') {
            return $label;
        }

        $address = $this->register_address;
        if ($address !== null && $address !== '') {
            return (string) $address;
        }

        return '—';
    }
}
