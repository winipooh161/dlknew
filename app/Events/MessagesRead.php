<?php
// app/Events/MessagesRead.php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessagesRead implements ShouldBroadcast
{
    use SerializesModels;

    public $chatId;
    public $userId;
    public $chatType;

    /**
     * Создание нового события.
     *
     * @return void
     */
    public function __construct($chatId, $userId, $chatType)
    {
        $this->chatId = $chatId;
        $this->userId = $userId;
        $this->chatType = $chatType;
    }

    /**
     * Получение канала на который будет транслироваться событие.
     *
     * @return Channel|PrivateChannel
     */
    public function broadcastOn()
    {
        if ($this->chatType === 'group') {
            return new PrivateChannel('chat.' . $this->chatId);
        } else {
            return new PrivateChannel('chat.personal.' . min($this->userId, $this->chatId) . '.' . max($this->userId, $this->chatId));
        }
    }

    /**
     * Имя события для фронтенда.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'messages.read';
    }
}
