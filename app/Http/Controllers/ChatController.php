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

use Illuminate\Support\Facades\Schema; // Добавляем импорт класса
use App\Services\ChatService; // Добавляем импорт класса
use App\Services\MessageService; // Добавляем импорт класса

use App\Http\Requests\Chat\SendMessageRequest; // добавленный импорт


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
            $personalChats = $this->getPersonalChats($user);
            $chats = collect();

            // Добавляем личные чаты в общий список, если они есть
            foreach ($personalChats as $relatedUser) {
                if ($relatedUser->chats->isNotEmpty()) {
                    foreach ($relatedUser->chats as $chat) {
                        $unreadCount = Message::where('chat_id', $chat->id)
                            ->where('sender_id', $relatedUser->id)
                            ->where('is_read', false)
                            ->count();

                        $chats->push([
                            'id' => $chat->id,
                            'type' => 'personal',
                            'name' => $relatedUser->name,
                            'avatar_url' => $relatedUser->avatar_url,
                            'unread_count' => $unreadCount,
                        ]);
                    }
                }
            }

            // Фильтруем групповые чаты в зависимости от роли пользователя
            $groupChats = Chat::where('type', 'group');

            switch ($user->status) {
                case 'partner':
                    $groupChats->whereHas('users', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
                    break;
                case 'architect':
                case 'designer':
                case 'visualizer':
                    $groupChats->whereHas('users', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
                    break;
                case 'coordinator':
                    // Для координатора показываем только те групповые чаты, где он является участником
                    $groupChats->whereHas('users', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
                    break;
                case 'admin':
                    // Админ видит все групповые чаты, ничего не фильтруем
                    break;
                default:
                    // Для остальных ролей не показываем групповые чаты
                    $groupChats->where('id', -1); // Пустой запрос, чтобы ничего не вернуть
                    break;
            }

            $groupChats = $groupChats->get();

            // Добавляем групповые чаты в общий список
            foreach ($groupChats as $chat) {
                $unreadCount = Message::where('chat_id', $chat->id)
                    ->where('sender_id', '!=', $user->id)
                    ->where('is_read', false)
                    ->count();

                $chats->push([
                    'id' => $chat->id,
                    'type' => 'group',
                    'name' => $chat->name,
                    'avatar_url' => $chat->avatar_url,
                    'unread_count' => $unreadCount,
                ]);
            }

            // Сортируем чаты по дате последнего сообщения (если необходимо)
            // $chats = $chats->sortByDesc('last_message_at');

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
            
            // Вычисляем максимальный ID сообщения для дальнейшего использования в подгрузке
            $lastMessageId = $messages->isEmpty() ? 0 : $messages->max('id');
            
            return $this->prepareChatResponse([
                'current_user_id' => $currentUserId,
                'messages'        => $formattedMessages,
                'last_message_id' => $lastMessageId, // Добавляем последний ID в ответ
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
            Log::info('Начало отправки сообщения', [
                'type' => $type,
                'id' => $id,
                'request' => $request->all(),
                'has_files' => $request->hasFile('attachments')
            ]);
            
            // Определяем ID чата
            $chatId = $type === 'group' ? $id : $this->getOrCreatePersonalChatId($id);
            
            // Получаем связанную сделку
            $chat = Chat::find($chatId);
            $dealId = $chat && $chat->deal_id ? $chat->deal_id : 'common';
            
            // Обработка вложений, если они есть
            $attachmentPaths = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    try {
                        Log::info('Загрузка файла', [
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getMimeType()
                        ]);
                        
                        // Сохраняем в подпапку по ID сделки
                        $path = $file->store("chat_attachments/{$dealId}", 'public');
                        
                        $attachmentPaths[] = [
                            'url' => asset('storage/' . $path),
                            'original_file_name' => $file->getClientOriginalName(),
                            'mime' => $file->getMimeType(),
                            'size' => $file->getSize(),
                            'path' => $path
                        ];
                    } catch (\Exception $e) {
                        Log::error('Ошибка при загрузке файла: ' . $e->getMessage(), [
                            'file' => $file->getClientOriginalName()
                        ]);
                    }
                }
            }
            
            // Создаем сообщение
            $message = Message::create([
                'chat_id' => $chatId,
                'sender_id' => Auth::id(),
                'message' => $request->message,
                'attachments' => $attachmentPaths,
                'is_read' => false
            ]);
            
            // Удален вызов FCM уведомлений, так как Firebase функциональность отключена.
            
            return $this->prepareChatResponse(['message' => new MessageResource($message)], 201);
        } catch (\Exception $e) {
            Log::error('Ошибка при отправке сообщения: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'type' => $type,
                'id' => $id,
                'request' => $request->all(),
            ]);
            return $this->prepareChatResponse(['error' => 'Ошибка при отправке сообщения: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Получает или создает ID личного чата между текущим пользователем и собеседником
     * 
     * @param int $otherUserId ID собеседника
     * @return int ID чата
     */
    private function getOrCreatePersonalChatId($otherUserId)
    {
        $currentUserId = Auth::id();
        
        // Ищем чат по полям sender_id и receiver_id
        $chat = Chat::where('type', 'personal')
            ->where(function ($query) use ($currentUserId, $otherUserId) {
                $query->where('sender_id', $currentUserId)
                      ->where('receiver_id', $otherUserId);
            })
            ->orWhere(function ($query) use ($currentUserId, $otherUserId) {
                $query->where('sender_id', $otherUserId)
                      ->where('receiver_id', $currentUserId);
            })
            ->first();
        
        // Если чат не существует, создаем его
        if (!$chat) {
            $chat = Chat::create([
                'type' => 'personal',
                'name' => 'Личный чат',
                'sender_id' => $currentUserId,
                'receiver_id' => $otherUserId,
            ]);
            // При наличии таблицы chat_user, добавляем обе записи
            $chat->participants()->attach([$currentUserId, $otherUserId]);
        }
        
        return $chat->id;
    }

    /**
     * Возвращает новые сообщения, отправленные после указанного ID.
     *
     * @param \Illuminate\Http\Request $request Запрос с параметрами
     * @param string $type Тип чата
     * @param int $id Идентификатор чата или пользователя
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNewMessages(Request $request, $type, $id)
    {
        $validated = $request->validate([
            'last_message_id' => 'nullable|integer|min:0',
        ]);

        $lastMessageId = $validated['last_message_id'] ?? 0;
        $currentUserId = Auth::id();

        try {
            Log::info('Запрос новых сообщений', [
                'type' => $type,
                'id' => $id,
                'last_message_id' => $lastMessageId,
                'current_user_id' => $currentUserId
            ]);
            
            // Определяем правильный ID чата в зависимости от типа
            $chatId = $id;
            if ($type === 'personal') {
                // Изменено: используем метод findPersonalChat вместо несуществующего getPersonalChatByUserId
                $chat = $this->findPersonalChat($currentUserId, $id);
                if (!$chat) {
                    Log::warning('Личный чат не найден', [
                        'current_user_id' => $currentUserId,
                        'other_user_id' => $id
                    ]);
                    return $this->prepareChatResponse(['error' => 'Чат не найден.'], 404);
                }
                $chatId = $chat->id;
                Log::info('Получен ID личного чата', ['chat_id' => $chatId]);
            }
            
            // Получаем новые сообщения (с ID больше последнего известного)
            $messages = Message::where('chat_id', $chatId)
                ->where('id', '>', $lastMessageId)
                ->orderBy('created_at', 'asc')
                ->get();
            
            Log::info('Получено новых сообщений', [
                'count' => $messages->count(),
                'chat_id' => $chatId,
                'last_message_id' => $lastMessageId
            ]);
            
            // Вычисляем максимальный ID сообщения для следующего запроса
            $maxMessageId = $messages->isEmpty() ? $lastMessageId : $messages->max('id');
            
            $formattedMessages = MessageResource::collection($messages);
            
            return $this->prepareChatResponse([
                'current_user_id' => $currentUserId,
                'messages' => $formattedMessages,
                'last_message_id' => $maxMessageId
            ]);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке новых сообщений', [
                'error' => $e->getMessage(),
                'chat_type' => $type,
                'chat_id' => $id,
                'last_message_id' => $lastMessageId,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->prepareChatResponse(['error' => 'Ошибка загрузки новых сообщений: ' . $e->getMessage()], 500);
        }
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
                // Получаем личный чат между текущим пользователем и другим
                $chat = app(\App\Services\MessageService::class)->getOrCreatePersonalChat($currentUserId, $id);
                // Добавляем логирование полученного чата
                Log::info('Получен личный чат для markMessagesAsRead', ['chat' => $chat, 'currentUserId' => $currentUserId, 'otherUserId' => $id]);
                
                $updatedRows = Message::where('chat_id', $chat->id)
                    ->where('sender_id', '!=', $currentUserId)
                    ->where('is_read', false)
                    ->update(['is_read' => true, 'read_at' => now()]);
                Log::info('Обновлено сообщений при markMessagesAsRead', ['updated' => $updatedRows]);
                
                event(new MessagesRead($chat->id, $currentUserId, $type));
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
            return response()->json(['success' => 'Сообщения отмечены как прочитанные.'], 200);
        } catch (\Exception $e) {
            Log::error('Ошибка при пометке сообщений как прочитанных: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return $this->prepareChatResponse(['error' => 'Ошибка при пометке сообщений как прочитанных.'], 500);
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
                // Изменено: получаем личный чат, чтобы обновить сообщения по chat_id
                $chat = app(\App\Services\MessageService::class)->getOrCreatePersonalChat($currentUserId, $id);
                if (!$chat) {
                    Log::error('Личный чат не найден', ['currentUserId' => $currentUserId, 'otherUserId' => $id]);
                    return response()->json(['error' => 'Личный чат не найден.'], 404);
                }
                Message::where('chat_id', $chat->id)
                    ->where('sender_id', $currentUserId)
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

            $unreadCounts = [
                'personal' => $personalUnreadCount,
                'group' => $groupUnreadCount,
            ];

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
                $chat = $this->getOrCreatePersonalChat($currentUserId, $chatId);
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
            // Изменено: сравниваем с фактическим ID чата
            if ($message->chat_id != $chat->id) {
                return response()->json(['error' => 'Сообщение не принадлежит данному чату.'], 400);
            }
            // Добавлено: проверка, что messageId - это число
            if (!is_numeric($messageId)) {
                return response()->json(['error' => 'Неверный ID сообщения.'], 400);
            }

            $message->is_pinned = true;
            $message->save();

            MessagePinLog::create([
                'user_id'    => Auth::id(),
                'message_id' => $message->id,
                'chat_id'    => $chat->id,
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

    // Изменяем метод deleteMessage для корректного получения параметров из URL
    public function deleteMessage($type, $chatId, $messageId)
    {
        try {
            // Проверяем, что сообщение принадлежит указанному чату
            $message = Message::where('chat_id', $chatId)->find($messageId);
            if (!$message) {
                return response()->json(['error' => 'Сообщение не найдено.'], 404);
            }
            $message->delete();
            return response()->json(['success' => 'Сообщение удалено.'], 200);
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении сообщения: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Ошибка при удалении сообщения.'], 500);
        }
    }

    // Изменяем функцию для поиска личного чата с использованием sender_id и receiver_id
    public function findPersonalChat($userId, $relatedId)
    {
        return Chat::where('type', 'personal')
            ->where(function ($query) use ($userId, $relatedId) {
                $query->where('sender_id', $userId)
                      ->where('receiver_id', $relatedId);
            })
            ->orWhere(function ($query) use ($userId, $relatedId) {
                $query->where('sender_id', $relatedId)
                      ->where('receiver_id', $userId);
            })
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
                $chat = $this->getOrCreatePersonalChat($currentUserId, $chatId);
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
            // Изменено: сравниваем с фактическим ID чата
            if ($message->chat_id != $chat->id) {
                return response()->json(['error' => 'Сообщение не принадлежит данному чату.'], 400);
            }
            // Добавлено: проверка, что messageId - это число
            if (!is_numeric($messageId)) {
                return response()->json(['error' => 'Неверный ID сообщения.'], 400);
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

    private function getOrCreatePersonalChat($currentUserId, $otherUserId)
    {
        // Ищем чат по полям sender_id и receiver_id
        $chat = Chat::where('type', 'personal')
            ->where(function ($query) use ($currentUserId, $otherUserId) {
                $query->where('sender_id', $currentUserId)
                      ->where('receiver_id', $otherUserId);
            })
            ->orWhere(function ($query) use ($currentUserId, $otherUserId) {
                $query->where('sender_id', $otherUserId)
                      ->where('receiver_id', $currentUserId);
            })
            ->first();
        
        // Если чат не существует, создаем его
        if (!$chat) {
            $chat = Chat::create([
                'type' => 'personal',
                'name' => 'Личный чат',
                'sender_id' => $currentUserId,
                'receiver_id' => $otherUserId,
            ]);
            // При наличии таблицы chat_user, добавляем обе записи
            $chat->participants()->attach([$currentUserId, $otherUserId]);
        }
        
        return $chat;
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
