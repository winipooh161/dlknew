<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Chat;
use App\Models\MessagePinLog; // Добавляем импорт класса
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Events\UserTyping; // Добавляем импорт класса
use App\Http\Resources\MessageResource;
use Illuminate\Support\Str; // Добавляем импорт класса
use Illuminate\Support\Facades\Http; // Добавляем импорт класса
use Illuminate\Support\Facades\Cache; // Добавлено
use Illuminate\Support\Facades\Schema; // Добавляем импорт класса
use App\Services\ChatService; // Добавляем импорт класса
use App\Services\MessageService; // Добавляем импорт класса
use App\Http\Requests\Chat\EditMessageRequest;
use App\Http\Requests\Chat\GetNewMessagesRequest;
use App\Http\Requests\Chat\UpdateTokenRequest;
use App\Http\Requests\Chat\SendMessageRequest; // добавленный импорт
use Purifier; // например, если используем библиотеку mews/purifier

class ChatController extends Controller
{
    protected $chatService;
    protected $messageService;

    public function __construct(ChatService $chatService, MessageService $messageService)
    {
        $this->chatService = $chatService;
        $this->messageService = $messageService;
    }

    /**
     * Получает личные чаты пользователя.
     *
     * @param \App\Models\User $user Текущий пользователь
     * @return \Illuminate\Support\Collection Список пользователей с личными чатами
     */
    private function getPersonalChats($user)
    {
        $userId = $user->id;
        switch ($user->status) {
            case 'admin':
            case 'coordinator':
                return User::where('id', '<>', $userId)
                    ->where('status', '<>', 'user')
                    ->with(['chats' => fn($q)=> $q->where('type', 'personal')])
                    ->get();
            case 'support':
                return User::where('id', '<>', $userId)
                    ->with(['chats' => fn($q)=> $q->where('type', 'personal')])
                    ->get();
            case 'user':
                $relatedDealIds = $user->deals()->pluck('deals.id');
                return User::whereIn('status', ['support','coordinator'])
                    ->whereHas('deals', fn($q)=> $q->whereIn('deals.id', $relatedDealIds))
                    ->where('id', '<>', $userId)
                    ->with(['chats' => fn($q)=> $q->where('type', 'personal')])
                    ->get();
            default:
                return collect();
        }
    }

    /**
     * Отображает список чатов (личных и групповых) для текущего пользователя.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title_site = "Чаты | Личный кабинет Экспресс-дизайн";
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        try {
            $chats = $this->chatService->getUserChats($user);
        } catch (\Exception $e) {
            Log::error('Ошибка при формировании списка чатов', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Ошибка при формировании списка чатов.'], 500);
        }

        return response()->view('chats', compact('chats', 'user', 'title_site'));
    }

    /**
     * Загружает сообщения для выбранного чата.
     *
     * @param string $type Тип чата ('personal' или 'group')
     * @param int $id Идентификатор чата или пользователя
     * @return \Illuminate\Http\JsonResponse
     */
    public function chatMessages(string $type, int $id): \Illuminate\Http\JsonResponse
    {
        $currentUserId = Auth::id();
        $perPage = 50;
        try {
            $messages = $this->messageService->getChatMessages($type, $id, $currentUserId, $perPage);
            $formattedMessages = MessageResource::collection($messages);
            return $this->prepareChatResponse([
                'current_user_id' => $currentUserId,
                'messages'        => $formattedMessages,
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке сообщений', [
                'error' => $e->getMessage(),
                'chat_type' => $type,
                'chat_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->prepareChatResponse(['error' => 'Ошибка загрузки сообщений.'], 500);
        }
    }

    /**
     * Отправляет сообщение в чат.
     *
     * @param \App\Http\Requests\Chat\SendMessageRequest $request Запрос с данными сообщения
     * @param string $type Тип чата ('personal' или 'group')
     * @param int $id Идентификатор чата или пользователя
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(SendMessageRequest $request, $type, $id)
    {
        try {
            $message = $this->messageService->sendMessage($request, $type, $id);
            $user = Auth::user();
            $chat = ($type === 'group') ? Chat::find($id) : null;
            $chatName = ($type === 'group' && $chat) ? $chat->name : $user->name;
            broadcast(new MessageSent($message, [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'chat_id' => $id,
                'chat_type' => $type,
                'chat_name' => $chatName,
            ]))->toOthers();

            // Можно расширить данные ответа для будущих настроек
            return $this->prepareChatResponse(['message' => new MessageResource($message)], 201);
        } catch (\Exception $e) {
            Log::error('Ошибка при отправке сообщения: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->prepareChatResponse(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Возвращает новые сообщения, отправленные после указанного ID.
     *
     * @param \App\Http\Requests\Chat\GetNewMessagesRequest $request Запрос с параметрами
     * @param string $type Тип чата
     * @param int $id Идентификатор чата или пользователя
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNewMessages(Request $request, $type, $id)
    {
        // Используем nullable вместо required для поля last_message_id
        $validated = $request->validate([
            'last_message_id' => 'nullable|integer|min:0',
        ]);
        
        $lastMessageId = $validated['last_message_id'] ?? 0;
        
        // Выбираем сообщения для указанного чата, которые новее указанного id
        $messages = Message::where('chat_id', $id)
            ->where('id', '>', $lastMessageId)
            ->orderBy('created_at', 'asc')
            ->get();
        
        return response()->json([
            'messages' => $messages,
            'current_user_id' => Auth::id(),
        ]);
    }

    /**
     * Помечает сообщения как прочитанные в указанном чате.
     *
     * @param string $type Тип чата
     * @param int $id Идентификатор чата или пользователя
     * @return \Illuminate\Http\JsonResponse
     */
    public function markMessagesAsRead($type, $id)
    {
        $currentUserId = Auth::id();

        try {
            if ($type === 'personal') {
                $otherUser = User::findOrFail($id);
                Message::where('sender_id', $otherUser->id)
                    ->where('receiver_id', $currentUserId)
                    ->where('is_read', false)
                    ->update(['is_read' => true, 'read_at' => now()]);
                event(new MessagesRead($id, $currentUserId, $type));
            } elseif ($type === 'group') {
                $chat = Chat::where('type', 'group')->findOrFail($id);
                if (!$chat->users->contains($currentUserId)) {
                    $chat->users()->attach($currentUserId);
                }
                Message::where('chat_id', $chat->id)
                    ->where('sender_id', '!=', $currentUserId)
                    ->where('is_read', false)
                    ->update(['is_read' => true, 'read_at' => now()]);
                $chat->users()->updateExistingPivot($currentUserId, ['last_read_at' => now()]);
                event(new MessagesRead($id, $currentUserId, $type));
            } else {
                return response()->json(['error' => 'Неверный тип чата.'], 400);
            }
            // Добавляем возврат успешного ответа
            return response()->json(['success' => 'Сообщения отмечены как прочитанные.'], 200);
        } catch (\Exception $e) {
            Log::error('Ошибка при пометке сообщений как прочитанных: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Ошибка при пометке сообщений как прочитанных.'], 500);
        }
    }

    /**
     * Отмечает сообщения как доставленные в указанном чате.
     *
     * @param string $type Тип чата
     * @param int $id Идентификатор чата или пользователя
     * @return \Illuminate\Http\JsonResponse
     */
    public function markMessagesDelivered($type, $id)
    {
        $currentUserId = Auth::id();

        try {
            if ($type === 'personal') {
                $otherUser = User::findOrFail($id);
                Message::where('sender_id', $currentUserId)
                    ->where('receiver_id', $otherUser->id)
                    ->whereNull('delivered_at')
                    ->update(['delivered_at' => now()]);
            } elseif ($type === 'group') {
                $chat = Chat::where('type', 'group')->findOrFail($id);
                Message::where('chat_id', $chat->id)
                    ->where('sender_id', $currentUserId)
                    ->whereNull('delivered_at')
                    ->update(['delivered_at' => now()]);
            } else {
                return response()->json(['error' => 'Неверный тип чата.'], 400);
            }

            return response()->json(['success' => 'Сообщения отмечены как доставленные.'], 200);
        } catch (\Exception $e) {
            Log::error('Ошибка при пометке сообщений как доставленных: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Ошибка при пометке сообщений как доставленных.'], 500);
        }
    }

    /**
     * Обрабатывает событие набора текста пользователем.
     *
     * @param Request $request
     * @param string $type Тип чата
     * @param int $id Идентификатор чата или пользователя
     * @return \Illuminate\Http\JsonResponse
     */
    public function typingIndicator(Request $request, $type, $id)
    {
        $user = Auth::user();
        $chatId = $id;

        try {
            broadcast(new UserTyping([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'chat_id' => $chatId,
                'chat_type' => $type,
                'typing' => true
            ]))->toOthers();

            return response()->json(['success' => 'Событие typing отправлено'], 200);
        } catch (\Exception $e) {
            Log::error('Ошибка при отправке события typing: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Ошибка при отправке события typing'], 500);
        }
    }

    /**
     * Получает количество непрочитанных сообщений для текущего пользователя.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadCounts()
    {
        $userId = Auth::id();

        try {
            $unreadCounts = Cache::remember("unread_counts_{$userId}", now()->addMinutes(2), function () use ($userId) {
                $personalUnreadCount = Message::where('receiver_id', $userId)
                    ->where('is_read', false)
                    ->count();

                $groupUnreadCount = 0;
                if (Schema::hasTable('chat_user')) {
                    $groupUnreadCount = DB::table('chat_user')
                        ->join('messages', 'chat_user.chat_id', '=', 'messages.chat_id')
                        ->where('chat_user.user_id', $userId)
                        ->where('messages.sender_id', '!=', $userId)
                        ->where('messages.is_read', false)
                        ->count();
                }

                return [
                    'personal' => $personalUnreadCount,
                    'group' => $groupUnreadCount,
                ];
            });

            return response()->json($unreadCounts);
        } catch (\Exception $e) {
            Log::error('Ошибка при получении количества непрочитанных сообщений: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Ошибка при получении количества непрочитанных сообщений.'], 500);
        }
    }

    /**
     * Закрепляет сообщение в чате.
     *
     * @param  string $type Тип чата ('personal' или 'group')
     * @param  int  $chatId
     * @param  int  $messageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function pinMessage(string $type, int $chatId, int $messageId)
    {
        try {
            if (!in_array($type, ['personal', 'group'])) {
                return response()->json(['error' => 'Неверный тип чата.'], 400);
            }
            
            if ($type === 'personal') {
                $currentUserId = Auth::id();
                // Изменено: используем user_id для личного чата
                $chat = Chat::whereHas('users', function ($query) use ($currentUserId, $chatId) {
                    $query->where('user_id', $currentUserId)
                          ->orWhere('user_id', $chatId);
                })->where('type', 'personal')->first();
                if (!$chat) {
                    return response()->json(['error' => 'Личный чат не найден.'], 404);
                }
            } elseif ($type === 'group') {
                $chat = Chat::where('id', $chatId)->where('type', 'group')->first();
                if (!$chat) {
                    return response()->json(['error' => 'Групповой чат не найден.'], 404);
                }
                if (!$chat->users()->where('user_id', Auth::id())->exists()) {
                    return response()->json(['error' => 'Вы не являетесь участником этого группового чата.'], 403);
                }
            }

            $message = Message::findOrFail($messageId);
            if ($message->chat_id != $chatId) {
                return response()->json(['error' => 'Сообщение не принадлежит данному чату.'], 400);
            }

            $message->is_pinned = true;
            $message->save();

            MessagePinLog::create([
                'user_id'    => Auth::id(),
                'message_id' => $message->id,
                'chat_id'    => $chatId,
                'action'     => 'pin',
            ]);

            return response()->json(['message' => 'Сообщение успешно закреплено.']);
        } catch (\Exception $e) {
            Log::error('Ошибка при закреплении сообщения: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Ошибка при закреплении сообщения: ' . $e->getMessage()], 500);
        }
    }

    // Добавляем метод deleteMessage для устранения ошибки "Method ... does not exist"
    public function deleteMessage($messageId)
    {
        try {
            $message = Message::findOrFail($messageId);
            $message->delete();
            return response()->json(['success' => 'Сообщение удалено.'], 200);
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении сообщения: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Ошибка при удалении сообщения.'], 500);
        }
    }

    // Изменена функция для поиска личного чата с использованием sender_id и receiver_id
    public function findPersonalChat($userId, $relatedId)
    {
        return Chat::where(function ($query) use ($userId, $relatedId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $relatedId);
        })->orWhere(function ($query) use ($userId, $relatedId) {
            $query->where('sender_id', $relatedId)
                  ->where('receiver_id', $userId);
        })->where('type', 'personal')
          ->first();
    }

    /**
     * Открепляет сообщение в чате.
     *
     * @param string $type Тип чата ('personal' или 'group')
     * @param int $chatId Идентификатор чата или пользователя
     * @param int $messageId Идентификатор сообщения
     * @return \Illuminate\Http\JsonResponse
     */
    public function unpinMessage(string $type, int $chatId, int $messageId)
    {
        try {
            if (!in_array($type, ['personal', 'group'])) {
                return response()->json(['error' => 'Неверный тип чата.'], 400);
            }
            if ($type === 'personal') {
                $currentUserId = Auth::id();
                // Изменено: используем user_id для личного чата
                $chat = Chat::whereHas('users', function ($query) use ($currentUserId, $chatId) {
                    $query->where('user_id', $currentUserId)
                          ->orWhere('user_id', $chatId);
                })->where('type', 'personal')->first();
                if (!$chat) {
                    return response()->json(['error' => 'Личный чат не найден.'], 404);
                }
            } elseif ($type === 'group') {
                $chat = Chat::where('id', $chatId)->where('type', 'group')->first();
                if (!$chat) {
                    return response()->json(['error' => 'Групповой чат не найден.'], 404);
                }
                if (!$chat->users()->where('user_id', Auth::id())->exists()) {
                    return response()->json(['error' => 'Вы не являетесь участником этого группового чата.'], 403);
                }
            }

            $message = Message::findOrFail($messageId);
            if ($message->chat_id != $chatId) {
                return response()->json(['error' => 'Сообщение не принадлежит данному чату.'], 400);
            }

            $message->is_pinned = false;
            $message->save();

            return response()->json(['success' => 'Сообщение успешно откреплено.'], 200);
        } catch (\Exception $e) {
            Log::error('Ошибка при откреплении сообщения: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Ошибка при откреплении сообщения.'], 500);
        }
    }

    // Новый метод для централизованной подготовки ответа чата с улучшенной структурой (улучшение 2, 21, 90)
    protected function prepareChatResponse(array $data, int $status = 200): \Illuminate\Http\JsonResponse
    {
        // Улучшение 101: Добавляем api_version из конфигурации
        return response()->json(
            array_merge([
                'timestamp'   => now()->toIso8601String(),
                'api_version' => config('app.api_version', '1.0'),
            ], $data),
            $status
        );
    }
}
