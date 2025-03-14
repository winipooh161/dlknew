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
            $otherUser = User::findOrFail($id);
            
            return Message::where(function ($q) use ($otherUser, $currentUserId) {
                    $q->where('sender_id', $currentUserId)
                      ->where('receiver_id', $otherUser->id);
                })
                ->orWhere(function ($q) use ($otherUser, $currentUserId) {
                    $q->where('sender_id', $otherUser->id)
                      ->where('receiver_id', $currentUserId);
                })
                ->orderBy('created_at', 'desc')
                ->with('sender')
                ->paginate($perPage);
        } elseif ($type === 'group') {
            $chat = Chat::where('type', 'group')->findOrFail($id);
            
            // Если пользователь не является участником чата, добавляем его
            if (!$chat->users->contains($currentUserId)) {
                $chat->users()->attach($currentUserId);
            }
            
            return Message::where('chat_id', $chat->id)
                ->orderBy('created_at', 'desc')
                ->with('sender')
                ->paginate($perPage);
        }
        
        throw new \InvalidArgumentException('Неверный тип чата');
    }
    
    /**
     * Отправляет сообщение в чат
     *
     * @param SendMessageRequest $request
     * @param string $type
     * @param int $id
     * @return Message
     */
    public function sendMessage(SendMessageRequest $request, string $type, int $id): Message
    {
        $currentUserId = Auth::id();
        $messageData = [
            'sender_id' => $currentUserId,
            'message' => $request->input('message'),
            'message_type' => 'text',
            'is_read' => false,
        ];
        
        // Проверяем наличие файлов и обрабатываем их
        if ($request->hasFile('attachments')) {
            $attachments = $this->handleFileUploads($request->file('attachments'));
            $messageData['attachments'] = $attachments;
            $messageData['message_type'] = 'file';
        }
        
        if ($type === 'personal') {
            $receiver = User::findOrFail($id);
            $messageData['receiver_id'] = $receiver->id;
        } elseif ($type === 'group') {
            $chat = Chat::findOrFail($id);
            $messageData['chat_id'] = $chat->id;
            
            // Если текущий пользователь не является участником чата, добавляем его
            if (!$chat->users->contains($currentUserId)) {
                $chat->users()->attach($currentUserId);
            }
        } else {
            throw new \InvalidArgumentException('Неверный тип чата');
        }
        
        $message = Message::create($messageData);
        
        // Отправляем уведомление через веб-сокеты
        broadcast(new MessageSent($message))->toOthers();
        
        return $message;
    }
    
    /**
     * Обрабатывает загрузку файлов
     *
     * @param array $files
     * @return array
     */
    private function handleFileUploads(array $files): array
    {
        $attachments = [];
        $allowedMime = config('constants.chat.supported_mime_types');
        
        foreach ($files as $file) {
            if (!in_array($file->getMimeType(), $allowedMime)) {
                continue; // Пропускаем неподдерживаемые файлы
            }
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('chat_attachments', $filename, 'public');
            
            $attachments[] = [
                'file_name' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'file_path' => Storage::url($path),
            ];
        }
        return $attachments;
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
