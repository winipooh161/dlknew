<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'type',
        'name',
        'slug',
        'deal_id',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Автоматически генерируем slug если он не был передан
        static::creating(function ($chat) {
            if (empty($chat->slug)) {
                $chat->slug = (string) Str::uuid();
            }
        });
    }

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
