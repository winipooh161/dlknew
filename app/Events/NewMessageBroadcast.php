<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class NewMessageBroadcast implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $message;
    public $dealId;

    public function __construct(Message $message, $dealId)
    {
        $this->message = $message;
        $this->dealId = $dealId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel("chat.{$this->dealId}");
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'deal_id' => $this->dealId,
        ];
    }
}
