<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    /**
     * Атрибуты, которые можно массово назначать.
     *
     * @var array
     */
    protected $fillable = [
        'sender_id',
        'receiver_id', // Изменено с recipient_id на receiver_id, чтобы соответствовать имени в БД
        'content',
        'attachments',
        'read_at',
    ];

    /**
     * Атрибуты, которые должны быть приведены к типам.
     *
     * @var array
     */
    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Получить отправителя сообщения.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Получить получателя сообщения.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id'); // Изменено с recipient_id на receiver_id
    }

    /**
     * Группа, в которой было отправлено сообщение (может быть NULL для личных сообщений)
     */
    public function chatGroup()
    {
        return $this->belongsTo(ChatGroup::class);
    }

    /**
     * Получить сообщения группового чата
     */
    public static function inChatGroup($chatGroupId)
    {
        return static::where('chat_group_id', $chatGroupId)
                    ->orderBy('created_at', 'asc');
    }

    /**
     * Проверяет, относится ли сообщение к групповому чату
     */
    public function isGroupMessage()
    {
        return !is_null($this->chat_group_id);
    }

    /**
     * Проверить, прочитано ли сообщение.
     *
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Отметить сообщение как прочитанное.
     *
     * @return void
     */
    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Получить сообщения между двумя пользователями.
     *
     * @param int $user1Id
     * @param int $user2Id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function betweenUsers($user1Id, $user2Id)
    {
        return static::where(function ($query) use ($user1Id, $user2Id) {
                $query->where('sender_id', $user1Id)
                    ->where('receiver_id', $user2Id);
            })->orWhere(function ($query) use ($user1Id, $user2Id) {
                $query->where('sender_id', $user2Id)
                    ->where('receiver_id', $user1Id);
            })
            ->whereNull('chat_group_id') // Только личные сообщения
            ->orderBy('created_at', 'asc');
    }

    /**
     * Получить последнее сообщение между двумя пользователями
     *
     * @param int $user1Id
     * @param int $user2Id
     * @return Message|null
     */
    public static function lastBetweenUsers($user1Id, $user2Id)
    {
        return static::betweenUsers($user1Id, $user2Id)
            ->latest()
            ->first();
    }

    /**
     * Получить количество непрочитанных сообщений от определенного пользователя
     *
     * @param int $fromUserId Отправитель
     * @param int $toUserId Получатель
     * @return int
     */
    public static function unreadCountBetweenUsers($fromUserId, $toUserId)
    {
        return static::where('sender_id', $fromUserId)
            ->where('receiver_id', $toUserId)
            ->whereNull('read_at')
            ->count();
    }
}
