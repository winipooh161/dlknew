<?php

// app/Events/MessageSent.php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $userData;

    /**
     * Создание нового события.
     *
     * @param  mixed  $message
     * @return void
     */
    public function __construct(Message $message, $userData = null)
    {
        $this->message = $message;
        $this->userData = $userData;
    }

    /**
     * Получение канала на который будет транслироваться событие.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        if ($this->message->chat_id) {
            return [
                new PrivateChannel('chat.' . $this->message->chat_id),
            ];
        } else {
            return [
                new PrivateChannel('user.' . $this->message->receiver_id),
            ];
        }
    }

    /**
     * Имя события для фронтенда.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'message.sent';
    }

    public function broadcastWith()
    {
        $data = [
            'message' => new \App\Http\Resources\MessageResource($this->message),
        ];

        if ($this->userData) {
            $data['userData'] = $this->userData;
        }

        return $data;
    }
}
