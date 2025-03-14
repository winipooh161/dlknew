<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class NewMessageNotification implements ShouldBroadcast
{
    use SerializesModels;

    public $message;
    public $chatType;
    public $chatId;

    /**
     * Создание нового события.
     *
     * @return void
     */
    public function __construct(Message $message, $chatType, $chatId)
    {
        $this->message = $message;
        $this->chatType = $chatType;
        $this->chatId = $chatId;
    }

    /**
     * Получение канала на который будет транслироваться событие.
     *
     * @return Channel|PrivateChannel
     */
    public function broadcastOn()
    {
        // Определяем канал в зависимости от типа чата
        if ($this->chatType === 'group') {
            return new PrivateChannel('chat.' . $this->chatId);
        } else {
            // Для личных чатов можно использовать канал пользователя
            // Предполагается, что $chatId — это ID пользователя
            return new PrivateChannel('user.' . $this->chatId);
        }
    }

    /**
     * Имя события для фронтенда.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'new.message.notification';
    }
}
