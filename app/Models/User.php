<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $status
 * @property string|null $city
 * @property string|null $phone
 * @property string|null $contract_number
 * @property string|null $comment
 * @property string|null $portfolio_link
 * @property int|null $experience
 * @property float|null $rating
 * @property int|null $active_projects_count
 * @property string|null $firebase_token
 * @property string|null $verification_code
 * @property \DateTime|null $verification_code_expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'city',
        'phone',
        'contract_number',
        'comment',
        'portfolio_link',
        'experience',
        'rating',
        'active_projects_count',
        'firebase_token',
        'verification_code',
        'verification_code_expires_at',
        'fcm_token',
        'last_seen_at', // Добавляем поле последней активности
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'string',
        'verification_code_expires_at' => 'datetime',
        'last_seen_at' => 'datetime', // Добавляем приведение типа
    ];

    public function deals()
    {
        return $this->hasMany(Deal::class, 'user_id');
    }

    public function responsibleDeals()
    {
        return $this->belongsToMany(Deal::class, 'deal_responsible', 'user_id', 'deal_id');
    }

    /**
     * Получить URL аватара пользователя
     * 
     * @return string
     */
    public function getAvatarUrlAttribute()
    {
        try {
            // Если есть profile_image и файл существует, используем его
            if ($this->profile_image && Storage::disk('public')->exists($this->profile_image)) {
                return asset('storage/' . $this->profile_image);
            }
            
            // Если avatar_url уже существует как аттрибут, используем его
            if (!empty($this->attributes['avatar_url'])) {
                return asset('' . ltrim($this->attributes['avatar_url'], ''));
            }
            
            // Проверяем, существует ли столбец avatar и есть ли в нем значение
            if (Schema::hasColumn('users', 'avatar') && !empty($this->attributes['avatar'])) {
                return asset('storage/' . $this->attributes['avatar']);
            }
            
            // Если ничего не найдено, возвращаем дефолтный аватар
        } catch (\Exception $e) {
            \Log::error('Ошибка при получении аватара: ' . $e->getMessage());
        }
        
        // Проверяем, существует ли файл дефолтного аватара
        $defaultPaths = [
            'storage/avatar/default.png',
            'storage/icon/profile.svg',
            'img/avatar-default.png'
        ];
        
        foreach ($defaultPaths as $path) {
            if (file_exists(public_path($path))) {
                return asset($path);
            }
        }
        
        // Запасной вариант - вернуть URL заглушки
        return 'https://via.placeholder.com/150';
    }

    public function isCoordinator()
    {
        return $this->status === 'coordinator';
    }

    public function coordinatorDeals()
    {
        return $this->belongsToMany(Deal::class, 'deal_user')
                    ->withPivot('role')
                    ->wherePivot('role', 'coordinator');
    }

    public function tokens()
    {
        return $this->hasMany(UserToken::class);
    }

    /**
     * Сообщения, отправленные пользователем
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Сообщения, полученные пользователем
     */
    public function receivedMessages()
    {
        // Проверяем, какое имя колонки существует
        if (Schema::hasColumn('messages', 'receiver_id')) {
            return $this->hasMany(Message::class, 'receiver_id');
        } else if (Schema::hasColumn('messages', 'recipient_id')) {
            return $this->hasMany(Message::class, 'recipient_id');
        }
        
        // По умолчанию используем receiver_id
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Получение всех сообщений пользователя (отправленных и полученных)
     */
    public function messages()
    {
        // Проверяем, какое имя колонки существует
        $receiverColumn = Schema::hasColumn('messages', 'receiver_id') 
            ? 'receiver_id' 
            : (Schema::hasColumn('messages', 'recipient_id') ? 'recipient_id' : 'receiver_id');
        
        return Message::where(function ($query) use ($receiverColumn) {
            $query->where('sender_id', $this->id)
                  ->orWhere($receiverColumn, $this->id);
        })->orderBy('created_at', 'desc');
    }

    /**
     * Получить количество непрочитанных сообщений для пользователя
     */
    public function unreadMessagesCount()
    {
        try {
            // Проверяем, существует ли таблица сообщений
            if (!Schema::hasTable('messages')) {
                return 0;
            }
            
            return $this->receivedMessages()
                ->whereNull('read_at')
                ->count();
        } catch (\Exception $e) {
            \Log::error('Ошибка при подсчете непрочитанных сообщений: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Группы пользователя
     */

    /**
     * Группы, в которых пользователь является администратором
     */
    public function adminChatGroups()
    {
        return $this->belongsToMany(ChatGroup::class, 'chat_group_users')
                    ->wherePivot('role', 'admin')
                    ->withTimestamps();
    }

    /**
     * Созданные пользователем группы
     */
    public function createdChatGroups()
    {
        return $this->hasMany(ChatGroup::class, 'created_by');
    }

    /**
     * Получить групповые чаты, в которых участвует пользователь.
     */
    public function chatGroups(): BelongsToMany
    {
        return $this->belongsToMany(ChatGroup::class, 'chat_group_user')
            ->withTimestamps()
            ->withPivot('role', 'is_admin');
    }

    /**
     * Получить сообщения в групповых чатах, отправленные пользователем.
     */
    public function groupMessages(): HasMany
    {
        return $this->hasMany(GroupMessage::class);
    }

    /**
     * Подсчитать количество непрочитанных сообщений в групповых чатах.
     */
    public function unreadGroupMessagesCount(): int
    {
        $readMessageIds = GroupMessageRead::where('user_id', $this->id)
            ->pluck('group_message_id');
        
        return GroupMessage::whereIn(
            'chat_group_id', 
            $this->chatGroups()->pluck('chat_groups.id')
        )
            ->whereNotIn('id', $readMessageIds)
            ->where('user_id', '!=', $this->id)
            ->count();
    }

    /**
     * Проверяет, находится ли пользователь в сети
     * Пользователь считается онлайн, если был активен за последние 5 минут
     * 
     * @return bool
     */
    public function isOnline()
    {
        if (!$this->last_seen_at) {
            return false;
        }
        
        // Считаем пользователя онлайн, если он был активен в последние 5 минут
        return $this->last_seen_at->gt(now()->subMinutes(5));
    }
    
    /**
     * Обновляет время последней активности пользователя
     */
    public function updateLastSeen()
    {
        $this->last_seen_at = now();
        $this->save();
    }
}
