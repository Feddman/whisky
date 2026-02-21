<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerJoined implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $sessionId,
        public int $participantId,
        public string $displayName,
        public bool $isGuest
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('tasting.session.'.$this->sessionId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'player.joined';
    }
}
