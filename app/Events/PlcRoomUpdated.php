<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dipancarkan setelah webhook PLC mengisi cache — Echo/Pusher di channel publik per testing_room.
 */
class PlcRoomUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $roomId,
        public array $snapshot,
        public string $status,
        public string $polledAt,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('plc.room.'.$this->roomId);
    }

    public function broadcastAs(): string
    {
        return 'PlcRoomUpdated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->roomId,
            'data' => $this->snapshot,
            'status' => $this->status,
            'polled_at' => $this->polledAt,
        ];
    }
}
