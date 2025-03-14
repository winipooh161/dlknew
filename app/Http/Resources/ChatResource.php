<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Преобразует информацию о чате в массив для JSON ответа.
     *
     * @param \Illuminate\Http\Request $request
     * @return array Структурированные данные чата
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'type'              => $this->type,
            'name'              => $this->name,
            'avatar_url'        => $this->avatar_url,
            'unread_count'      => $this->unread_count,
            'last_message_time' => $this->last_message_time,
            'last_message'      => $this->messages->first()->message ?? null,
            'participants_count' => $this->users->count(),
        ];
    }
}
