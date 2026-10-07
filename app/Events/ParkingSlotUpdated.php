<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParkingSlotUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $slots;
    public int $totalAvailable;

    public function __construct(array $slots, int $totalAvailable)
    {
        $this->slots = $slots;
        $this->totalAvailable = $totalAvailable;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('parking-tracker'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'slot.updated';
    }
}