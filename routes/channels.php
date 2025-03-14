<?php

// routes/channels.php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use App\Models\Chat;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Приватный канал для групповых чатов
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);
    if (!$chat) return false;
    return $chat->users->contains($user->id);
});

// Приватный канал для личных чатов
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Канал для уведомлений
Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('chat.{chatType}.{chatId}', function (User $user, $chatType, $chatId) {
    return $user->id !== null;
});
