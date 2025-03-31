<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupMessageRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_message_id',
        'user_id',
    ];

    /**
     * Получить сообщение, которое было прочитано.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(GroupMessage::class, 'group_message_id');
    }

    /**
     * Получить пользователя, который прочитал сообщение.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
