<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'avatar',
        'created_by',
    ];

    /**
     * Получить пользователей, которые участвуют в группе.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_group_user')
            ->withTimestamps()
            ->withPivot('is_admin');
    }

    /**
     * Получить сообщения в группе.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(GroupMessage::class);
    }

    /**
     * Получить создателя группы.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Проверить, является ли указанный пользователь администратором группы.
     */
    public function isAdmin($userId): bool
    {
        return $this->users()
            ->wherePivot('user_id', $userId)
            ->wherePivot('role', 'admin')
            ->orWherePivot('is_admin', true)
            ->exists();
    }

    /**
     * Проверить, является ли указанный пользователь участником группы.
     */
    public function isMember($userId): bool
    {
        return $this->users()->where('user_id', $userId)->exists();
    }
}
