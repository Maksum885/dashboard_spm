<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomCamera extends Model
{
    protected $fillable = [
        'testing_room_id',
        'slot',
        'name',
        'rtsp_host',
        'rtsp_port',
        'rtsp_path',
        'rtsp_username',
        'rtsp_password',
        'is_enabled',
        'stream_url',
    ];

    protected function casts(): array
    {
        return [
            'slot' => 'integer',
            'rtsp_port' => 'integer',
            'is_enabled' => 'boolean',
        ];
    }

    public function testingRoom(): BelongsTo
    {
        return $this->belongsTo(TestingRoom::class);
    }

    public function buildRtspUrl(): string
    {
        $user = rawurlencode((string) ($this->rtsp_username ?? ''));
        $pass = rawurlencode((string) ($this->rtsp_password ?? ''));
        $host = $this->rtsp_host;
        $port = (int) $this->rtsp_port;
        $path = ltrim((string) $this->rtsp_path, '/');

        if ($user !== '' || $pass !== '') {
            return "rtsp://{$user}:{$pass}@{$host}:{$port}/{$path}";
        }

        return "rtsp://{$host}:{$port}/{$path}";
    }
}
