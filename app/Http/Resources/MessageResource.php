<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MessageResource extends JsonResource
{
    /**
     * Преобразует данные сообщения в массив для JSON ответа.
     *
     * @param \Illuminate\Http\Request $request
     * @return array Массив с информацией о сообщении и вложенных данных
     */
    public function toArray($request)
    {
        $chatType = $this->chat ? $this->chat->type : 'personal';
        $chatId = $this->chat_id ?? $this->receiver_id;
        
        // Проверяем и правильно обрабатываем вложения
        $attachments = $this->getProcessedAttachments();

        return [
            'id'                 => $this->id,
            'sender_id'          => $this->sender_id,
            'receiver_id'        => $this->receiver_id,
            'chat_id'            => $this->chat_id,
            'message'            => $this->message,
            'is_read'            => $this->is_read,
            'read_at'            => $this->read_at,
            'delivered'          => !empty($this->delivered_at),
            'sender_name'        => $this->sender->name ?? 'Unknown',
            'sender_avatar'      => $this->sender->avatar_url ?? '/user/avatar/default.png',
            'is_pinned'          => $this->is_pinned,
            'message_type'       => $this->message_type, // ('text', 'file' или 'notification')
            'attachments'        => $attachments,
            'created_at'         => $this->created_at->toDateTimeString(),
            'message_link'       => route('chats.messages', ['chatType' => $chatType, 'chatId' => $chatId]) . "#message-{$this->id}",
        ];
    }
    
    /**
     * Получает обработанные вложения для сообщения
     *
     * @return array
     */
    protected function getProcessedAttachments()
    {
        // Проверяем, что attachments является массивом или JSON строкой
        $attachments = [];
        
        if (!empty($this->attachments)) {
            if (is_string($this->attachments)) {
                // Если строка, пробуем распарсить JSON
                try {
                    $decoded = json_decode($this->attachments, true);
                    $attachments = is_array($decoded) ? $decoded : [];
                } catch (\Exception $e) {
                    Log::error('Ошибка при декодировании attachments: ' . $e->getMessage(), ['attachments' => $this->attachments]);
                    return [];
                }
            } elseif (is_array($this->attachments)) {
                $attachments = $this->attachments;
            }
        }

        // Преобразуем старый формат вложений в новый, если нужно
        $result = [];
        foreach ($attachments as $attachment) {
            if (is_string($attachment)) {
                // Старый формат: просто строка с путем
                if (Storage::disk('public')->exists($attachment)) {
                    $result[] = [
                        'url' => Storage::url($attachment),
                        'mime' => Storage::mimeType($attachment),
                        'original_file_name' => basename($attachment),
                        'size' => Storage::size($attachment)
                    ];
                }
            } elseif (is_array($attachment)) {
                // Новый формат: массив с полями
                if (isset($attachment['url'])) {
                    $result[] = $attachment;
                } elseif (isset($attachment['path']) && Storage::disk('public')->exists($attachment['path'])) {
                    $attachment['url'] = Storage::url($attachment['path']);
                    $result[] = $attachment;
                }
            }
        }
        
        return $result;
    }
}
