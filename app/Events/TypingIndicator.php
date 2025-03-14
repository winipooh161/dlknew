<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TypingIndicator implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $chatId;
    public $userName;
    public $typing;

    /**
     * Создание нового события.
     *
     * @param mixed $chatId
     * @param string $userName
     * @param bool $typing
     * @return void
     */
    public function __construct($chatId, $userName, $typing = true)
    {
        $this->chatId = $chatId;
        $this->userName = $userName;
        $this->typing = $typing;
    }

    /**
     * Получение каналов, на которые транслируется событие.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->chatId);
    }

    /**
     * Имя события для фронтенда.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'typing.indicator';
    }
}
