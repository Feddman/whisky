<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ParticipantUpdated implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public int $sessionId;
    public int $participantId;
    public ?string $displayName;
    public ?string $avatarSeed;

    public function __construct(int $sessionId, int $participantId, ?string $displayName = null, ?string $avatarSeed = null)
    {
        $this->sessionId = $sessionId;
        $this->participantId = $participantId;
        $this->displayName = $displayName;
        $this->avatarSeed = $avatarSeed;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('tasting.session.'.$this->sessionId);
    }

    public function broadcastWith(): array
    {
        return [
            'participantId' => $this->participantId,
            'displayName' => $this->displayName,
            'avatarSeed' => $this->avatarSeed,
        ];
    }

    public function broadcastAs(): string
    {
        return 'participant.updated';
    }
}
