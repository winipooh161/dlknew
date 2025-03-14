<?php

namespace App\Services;

use App\Models\User;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ChatService
{
    /**
     * Получает чаты пользователя в зависимости от его роли
     *
     * @param User $user
     * @return Collection
     */
    public function getUserChats(User $user): Collection
    {
        $userId = $user->id;
        $cacheKey = "user_chats_{$userId}";
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        try {
            $personalChats = $this->getPersonalChats($user);
            $groupChats = $this->getGroupChats($userId);
            
            $chats = $this->formatAndSortChats($personalChats, $groupChats, $userId);
            
            Cache::put($cacheKey, $chats, now()->addMinutes(5));
            return $chats;
        } catch (\Exception $e) {
            Log::error('Ошибка при получении чатов пользователя', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return collect();
        }
    }
    
    /**
     * Получает личные чаты пользователя в зависимости от его роли
     *
     * @param User $user
     * @return Collection
     */
    private function getPersonalChats(User $user): Collection
    {
        $userId = $user->id;
        
        switch ($user->status) {
            case 'admin':
            case 'coordinator':
                return User::where('id', '<>', $userId)
                    ->where('status', '<>', 'user')
                    ->with(['chats' => fn($q) => $q->where('type', 'personal')])
                    ->get();
            case 'support':
                return User::where('id', '<>', $userId)
                    ->with(['chats' => fn($q) => $q->where('type', 'personal')])
                    ->get();
            case 'user':
                $relatedDealIds = $user->deals()->pluck('deals.id');
                return User::whereIn('status', ['support', 'coordinator'])
                    ->whereHas('deals', fn($q) => $q->whereIn('deals.id', $relatedDealIds))
                    ->where('id', '<>', $userId)
                    ->with(['chats' => fn($q) => $q->where('type', 'personal')])
                    ->get();
            default:
                return collect();
        }
    }
    
    /**
     * Получает групповые чаты пользователя
     *
     * @param int $userId
     * @return Collection
     */
    private function getGroupChats(int $userId): Collection
    {
        return Chat::where('type', 'group')
            ->whereHas('users', function($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->with(['messages' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(1);
            }])
            ->with('users')
            ->get();
    }
    
    /**
     * Форматирует и сортирует чаты для отображения
     *
     * @param Collection $personalChats
     * @param Collection $groupChats
     * @param int $userId
     * @return Collection
     */
    private function formatAndSortChats(Collection $personalChats, Collection $groupChats, int $userId): Collection
    {
        $chats = collect();
        
        // Обработка личных чатов
        foreach ($personalChats as $chatUser) {
            $unreadCount = Message::where('sender_id', $chatUser->id)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->count();
                
            $lastMessage = Message::where(function ($q) use ($chatUser, $userId) {
                    $q->where('sender_id', $userId)
                      ->where('receiver_id', $chatUser->id);
                })
                ->orWhere(function ($q) use ($chatUser, $userId) {
                    $q->where('sender_id', $chatUser->id)
                      ->where('receiver_id', $userId);
                })
                ->orderBy('created_at', 'desc')
                ->first();
                
            $avatarUrl = $chatUser->avatar_url;
            if (empty($avatarUrl) || !file_exists(public_path($avatarUrl))) {
                $avatarUrl = 'storage/avatars/user_default.png';
            }
            
            $chats->push([
                'id' => $chatUser->id,
                'type' => 'personal',
                'name' => $chatUser->name,
                'avatar_url' => $avatarUrl,
                'unread_count' => $unreadCount,
                'last_message_time' => $lastMessage ? $lastMessage->created_at : null,
            ]);
        }
        
        // Обработка групповых чатов
        foreach ($groupChats as $chat) {
            $pivot = $chat->users->find($userId)->pivot ?? null;
            $lastReadAt = $pivot ? $pivot->last_read_at : null;
            
            $unreadCount = $lastReadAt
                ? $chat->messages->where('created_at', '>', $lastReadAt)->where('sender_id', '!=', $userId)->count()
                : $chat->messages->where('sender_id', '!=', $userId)->count();
                
            $lastMessage = $chat->messages->first();
            
            $avatarUrl = $chat->avatar_url;
            if (empty($avatarUrl) || !file_exists(public_path($avatarUrl))) {
                $avatarUrl = 'storage/avatars/group_default.svg';
            }
            
            $chats->push([
                'id' => $chat->id,
                'type' => 'group',
                'name' => $chat->name,
                'avatar_url' => $avatarUrl,
                'unread_count' => $unreadCount,
                'last_message_time' => $lastMessage ? $lastMessage->created_at : null,
            ]);
        }
        
        return $chats->sortByDesc(function ($chat) {
            return $chat['unread_count'] > 0 ? 1 : 0;
        })->sortByDesc('last_message_time')->values();
    }
    
    /**
     * Создает новый групповой чат
     *
     * @param array $data
     * @return Chat
     */
    public function createGroupChat(array $data): Chat
    {
        $chat = Chat::create([
            'name' => $data['name'],
            'type' => 'group',
            'avatar_url' => $data['avatar_url'] ?? null,
        ]);
        
        $chat->users()->attach($data['user_ids']);
        
        // Добавляем системное сообщение о создании чата
        $creatorId = Auth::id();
        Message::create([
            'sender_id' => $creatorId,
            'chat_id' => $chat->id,
            'message' => 'Групповой чат создан',
            'message_type' => 'notification',
            'is_system' => true,
        ]);
        
        return $chat;
    }
}
