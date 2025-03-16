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
        
        // Проверяем, что attachments является массивом
        $attachments = [];
        if (!empty($this->attachments)) {
            if (is_string($this->attachments)) {
                // Если строка, пробуем распарсить JSON
                try {
                    $decoded = json_decode($this->attachments, true);
                    $attachments = is_array($decoded) ? $decoded : [];
                } catch (\Exception $e) {
                    $attachments = [];
                    Log::error('Ошибка при декодировании attachments: ' . $e->getMessage(), ['attachments' => $this->attachments]);
                }
            } elseif (is_array($this->attachments)) {
                $attachments = $this->attachments;
            }
        }

        $attachmentUrls = [];
        if ($attachments) {
            foreach ($attachments as $attachment) {
                // Проверяем, что attachment является строкой, прежде чем использовать Storage::exists()
                if (is_string($attachment)) {
                    if (Storage::disk('public')->exists($attachment)) {
                        $attachmentUrls[] = [
                            'url' => Storage::url($attachment),
                            'mime' => Storage::mimeType($attachment),
                            'original_file_name' => basename($attachment), // Extract file name
                        ];
                    } else {
                        Log::warning('Файл не найден: ' . $attachment);
                        $attachmentUrls[] = null; // Или можно использовать какое-то значение по умолчанию
                    }
                } else {
                    Log::warning('Неверный формат attachment: ' . print_r($attachment, true));
                }
            }
        }

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
            'attachments'        => $attachmentUrls,
            'created_at'         => $this->created_at->toDateTimeString(),
            'message_link'       => route('chats.messages', ['chatType' => $chatType, 'chatId' => $chatId]) . "#message-{$this->id}",
        ];
    }
}
