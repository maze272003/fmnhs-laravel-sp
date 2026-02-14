<?php

namespace App\Events;

use App\Models\ConferenceParticipant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConferenceJoined
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ConferenceParticipant $participant;

    public array $deviceInfo;

    public function __construct(ConferenceParticipant $participant, array $deviceInfo = [])
    {
        $this->participant = $participant;
        $this->deviceInfo = $deviceInfo;
    }
}
