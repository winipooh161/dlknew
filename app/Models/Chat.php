<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'type',
        'name',
        'slug',
    ];

    /**
     * Пользователи, участвующие в чате.
     */
    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Сообщения в чате.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Сделка, связанная с чатом.
     */
    public function deal()
    {
        return $this->belongsTo(Deal::class, 'deal_id', 'id');
    }

    /**
     * Закреплённые сообщения в чате.
     */
    public function pinnedMessages()
    {
        return $this->hasMany(Message::class)->where('is_pinned', true);
    }
}
