<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Events\MessageSent;
use App\Http\Requests\Chat\SendMessageRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MessageService
{
    /**
     * Получает сообщения для выбранного чата с пагинацией
     *
     * @param string $type
     * @param int $id
     * @param int $currentUserId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getChatMessages(string $type, int $id, int $currentUserId, int $perPage = 50): LengthAwarePaginator
    {
        if ($type === 'personal') {
            // Используем getOrCreatePersonalChat для получения корректного chat_id
            $chat = $this->getOrCreatePersonalChat($currentUserId, $id);
            $messages = Message::where('chat_id', $chat->id)
                ->orderBy('created_at', 'desc')
                ->with('sender')
                ->paginate($perPage);
            // Сортировка сообщений по возрастанию времени создания
            $messages->setCollection($messages->getCollection()->sortBy('created_at'));
            return $messages;
        } elseif ($type === 'group') {
            $chat = Chat::where('type', 'group')->findOrFail($id);
            
            // Если пользователь не является участником чата, добавляем его
            if (!$chat->users->contains($currentUserId)) {
                $chat->users()->attach($currentUserId);
            }
            
            $messages = Message::where('chat_id', $chat->id)
                ->orderBy('created_at', 'desc')
                ->with('sender')
                ->paginate($perPage);
            // Изменено: сортировка сообщений по возрастанию времени создания
            $messages->setCollection($messages->getCollection()->sortBy('created_at'));
            return $messages;
        }
        
        throw new \InvalidArgumentException('Неверный тип чата');
    }
    
    /**
     * Отправляет сообщение в чат.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $type Тип чата ('personal' или 'group')
     * @param int $id Идентификатор чата или пользователя
     * @return \App\Models\Message
     */
    public function sendMessage(Request $request, string $type, int $id): Message
    {
        $user = Auth::user();
        $messageText = $request->input('message');
        $attachments = $request->file('attachments', []);
        
        if ($type === 'personal') {
            $chat = $this->getOrCreatePersonalChat($user->id, $id);
            $receiverId = $id;
        } elseif ($type === 'group') {
            $chat = Chat::findOrFail($id);
            $receiverId = null;
        } else {
            throw new \InvalidArgumentException('Неверный тип чата.');
        }

        $message = new Message([
            'chat_id'    => $chat->id,
            'sender_id'  => $user->id,
            'receiver_id'=> $receiverId,
            'message'    => $messageText,
        ]);

        // Обработка вложений
        $storedAttachments = [];
        if ($attachments) {
            if (!is_array($attachments)) {
                $attachments = [$attachments]; // гарантируем, что attachments - массив
            }
            foreach ($attachments as $file) {
                // Если $file является массивом или не является экземпляром UploadedFile, пропускаем его
                if (is_array($file) || !($file instanceof \Illuminate\Http\UploadedFile)) {
                    continue;
                }
                $path = Storage::disk('public')->putFile('attachments', $file);
                $storedAttachments[] = $path;
            }
        }

        if (!empty($storedAttachments)) {
            $message->attachments = json_encode($storedAttachments, JSON_UNESCAPED_SLASHES);
        }

        $message->save();

        return $message;
    }

    /**
     * Создает или возвращает существующий личный чат между двумя пользователями.
     *
     * @param int $userId
     * @param int $relatedId
     * @return Chat
     */
    public function getOrCreatePersonalChat(int $userId, int $relatedId): Chat
    {
        $chat = Chat::where(function ($query) use ($userId, $relatedId) {
            $query->where('sender_id', $userId)
                ->where('receiver_id', $relatedId);
        })->orWhere(function ($query) use ($userId, $relatedId) {
            $query->where('sender_id', $relatedId)
                ->where('receiver_id', $userId);
        })->where('type', 'personal')->first();

        if (!$chat) {
            Log::info('Создание нового личного чата', ['senderId' => $userId, 'receiverId' => $relatedId]);
            $chat = Chat::create([
                'sender_id' => $userId,
                'receiver_id' => $relatedId,
                'type' => 'personal',
                'name' => 'Personal Chat', // Можно сделать имя динамическим
                'slug' => Str::uuid(),
            ]);
        } else {
            Log::info('Личный чат найден', ['chatId' => $chat->id]);
        }

        return $chat;
    }

    /**
     * Помечает сообщения как прочитанные
     *
     * @param string $type
     * @param int $id
     * @param int $currentUserId
     * @return bool
     */
    public function markMessagesAsRead(string $type, int $id, int $currentUserId): bool
    {
        if ($type === 'personal') {
            $otherUser = User::findOrFail($id);
            
            Message::where('sender_id', $otherUser->id)
                ->where('receiver_id', $currentUserId)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
                
            return true;
        } elseif ($type === 'group') {
            $chat = Chat::where('type', 'group')->findOrFail($id);
            
            if (!$chat->users->contains($currentUserId)) {
                $chat->users()->attach($currentUserId);
            }
            
            Message::where('chat_id', $chat->id)
                ->where('sender_id', '!=', $currentUserId)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);
                
            $chat->users()->updateExistingPivot($currentUserId, ['last_read_at' => now()]);
            
            return true;
        }
        
        return false;
    }
}
