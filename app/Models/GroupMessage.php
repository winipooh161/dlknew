<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_group_id',
        'user_id',
        'message',
        'attachments',
        'is_system',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_system' => 'boolean',
    ];

    /**
     * Получить группу, к которой принадлежит сообщение.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ChatGroup::class, 'chat_group_id');
    }

    /**
     * Получить пользователя, который отправил сообщение.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить прочтения этого сообщения.
     */
    public function reads(): HasMany
    {
        return $this->hasMany(GroupMessageRead::class);
    }

    /**
     * Отметить сообщение как прочитанное указанным пользователем.
     */
    public function markAsRead(User $user)
    {
        return GroupMessageRead::firstOrCreate([
            'group_message_id' => $this->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Проверить, прочитано ли сообщение указанным пользователем.
     */
    public function isReadBy(User $user): bool
    {
        return $this->reads()->where('user_id', $user->id)->exists();
    }

    /**
     * Получить количество непрочитанных сообщений для указанного пользователя.
     */
    public static function unreadCountForUser(User $user, ChatGroup $group): int
    {
        $readMessageIds = GroupMessageRead::where('user_id', $user->id)
            ->pluck('group_message_id');
        
        return static::where('chat_group_id', $group->id)
            ->whereNotIn('id', $readMessageIds)
            ->where('user_id', '!=', $user->id)
            ->count();
    }
}
