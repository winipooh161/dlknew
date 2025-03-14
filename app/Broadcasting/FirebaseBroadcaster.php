<?php

namespace App\Broadcasting;

use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Http\Request;

class FirebaseBroadcaster extends Broadcaster
{
    public function auth($request)
    {
        // Простая авторизация – возвращаем текущего пользователя
        return $request->user();
    }

    public function validAuthenticationResponse($request, $result)
    {
        return $result;
    }
    
    public function broadcast(array $channels, $event, array $payload = [])
    {
        // Реализуйте отправку сообщений через Firebase (пример заглушки)
        logger()->info("Firebase broadcast", compact('channels', 'event', 'payload'));
    }
}
