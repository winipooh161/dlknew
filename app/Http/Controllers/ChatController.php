<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use App\Models\ChatGroup; // Добавляем импорт модели ChatGroup
use App\Models\GroupMessage;
use App\Models\GroupMessageRead;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Schema;

class ChatController extends Controller
{
    public function index()
    {
        return view('chats');
    }

    public function getContacts()
    {
        $user = auth()->user();
        
        // Обновляем статус текущего пользователя
        $user->updateLastSeen();
        
        $contacts = User::where('id', '!=', $user->id)->get()->map(function ($contact) {
            // Получаем URL аватарки, устанавливаем дефолтную, если не найдена
            $avatarUrl = $contact->profile_image ? asset('storage/' . $contact->profile_image) : asset('storage/icon/profile.svg');
            
            // Определяем статус пользователя на основе его последней активности
            $status = $contact->isOnline() ? 'online' : 'offline';
            
            // Форматируем информацию о последней активности
            $lastActivity = $contact->last_seen_at 
                ? $this->formatLastActivity($contact->last_seen_at) 
                : 'Неизвестно';
            
            return [
                'id' => $contact->id,
                'name' => $contact->name,
                'avatar' => $avatarUrl,
                'status' => $status,
                'lastMessage' => 'Нет сообщений', // Заглушка, можно сделать реальный запрос
                'unreadCount' => 0, // Заглушка, можно сделать реальный запрос
                'lastActivity' => $lastActivity,
            ];
        });
        
        return response()->json($contacts);
    }

    protected function formatLastActivity($dateTime)
    {
        $now = now();
        $lastSeen = \Carbon\Carbon::parse($dateTime);
        
        if ($lastSeen->isToday()) {
            return 'Сегодня, ' . $lastSeen->format('H:i');
        } elseif ($lastSeen->isYesterday()) {
            return 'Вчера, ' . $lastSeen->format('H:i');
        } elseif ($now->diffInDays($lastSeen) < 7) {
            $days = [
                'Понедельник', 'Вторник', 'Среда', 'Четверг',
                'Пятница', 'Суббота', 'Воскресенье'
            ];
            return $days[$lastSeen->dayOfWeek - 1] . ', ' . $lastSeen->format('H:i');
        } else {
            return $lastSeen->format('d.m.Y H:i');
        }
    }

    public function getMessages($id)
    {
        $currentUser = Auth::user();
        
        try {
            $recipient = User::findOrFail($id);
            
            // Получаем сообщения между пользователями
            $messages = Message::where(function ($query) use ($currentUser, $recipient) {
                    $query->where('sender_id', $currentUser->id)
                        ->where('receiver_id', $recipient->id); // Изменено с recipient_id на receiver_id
                })->orWhere(function ($query) use ($currentUser, $recipient) {
                    $query->where('sender_id', $recipient->id)
                        ->where('receiver_id', $currentUser->id); // Изменено с recipient_id на receiver_id
                })
                ->orderBy('created_at')
                ->get()
                ->map(function ($message) {
                    // Подготовка вложений, если есть
                    $attachments = [];
                    if ($message->attachments) {
                        foreach (json_decode($message->attachments, true) as $attachment) {
                            $attachments[] = [
                                'name' => $attachment['name'],
                                'url' => asset('storage/' . $attachment['path']),
                                'type' => $attachment['type'] ?? 'file'
                            ];
                        }
                    }
                    
                    return [
                        'id' => $message->id,
                        'sender_id' => $message->sender_id,
                        'content' => $message->content,
                        'attachments' => $attachments,
                        'created_at' => $message->created_at,
                        'read_at' => $message->read_at
                    ];
                });
            
            // Отмечаем сообщения как прочитанные
            Message::where('sender_id', $recipient->id)
                ->where('receiver_id', $currentUser->id) // Изменено с recipient_id на receiver_id
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
            
            return response()->json([
                'messages' => $messages
            ]);
        } catch (ModelNotFoundException $e) {
            // Пользователь не найден
            return response()->json([
                'error' => 'Пользователь не найден'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Ошибка при получении сообщений: ' . $e->getMessage());
            return response()->json([
                'error' => 'Произошла ошибка при получении сообщений'
            ], 500);
        }
    }

    public function getNewMessages($id, Request $request)
    {
        $currentUser = Auth::user();
        $lastId = $request->input('last_id', 0);
        
        try {
            $recipient = User::findOrFail($id);
            
            // Получаем только новые сообщения
            $messages = Message::where(function ($query) use ($currentUser, $recipient) {
                    $query->where('sender_id', $currentUser->id)
                        ->where('receiver_id', $recipient->id); // Изменено с recipient_id на receiver_id
                })->orWhere(function ($query) use ($currentUser, $recipient) {
                    $query->where('sender_id', $recipient->id)
                        ->where('receiver_id', $currentUser->id); // Изменено с recipient_id на receiver_id
                })
                ->where('id', '>', $lastId)
                ->orderBy('created_at')
                ->get()
                ->map(function ($message) {
                    // Подготовка вложений, если есть
                    $attachments = [];
                    if ($message->attachments) {
                        foreach (json_decode($message->attachments, true) as $attachment) {
                            $attachments[] = [
                                'name' => $attachment['name'],
                                'url' => asset('storage/' . $attachment['path']),
                                'type' => $attachment['type'] ?? 'file'
                            ];
                        }
                    }
                    
                    return [
                        'id' => $message->id,
                        'sender_id' => $message->sender_id,
                        'content' => $message->content,
                        'attachments' => $attachments,
                        'created_at' => $message->created_at,
                        'read_at' => $message->read_at
                    ];
                });
            
            // Отмечаем сообщения как прочитанные только если они от другого пользователя
            Message::where('sender_id', $recipient->id)
                ->where('receiver_id', $currentUser->id) // Изменено с recipient_id на receiver_id
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
            
            return response()->json([
                'messages' => $messages
            ]);
        } catch (ModelNotFoundException $e) {
            // Пользователь не найден
            return response()->json([
                'error' => 'Пользователь не найден'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Ошибка при получении новых сообщений: ' . $e->getMessage());
            return response()->json([
                'error' => 'Произошла ошибка при получении новых сообщений'
            ], 500);
        }
    }

    public function sendMessage($id, Request $request)
    {
        try {
            $currentUser = Auth::user();
            // Обновляем статус текущего пользователя
            $currentUser->updateLastSeen();
            
            $recipient = User::findOrFail($id);
            
            $validated = $request->validate([
                'message' => 'nullable|string|max:2000',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|max:10240' // макс. 10MB
            ]);
            
            // Проверка наличия сообщения или вложений
            if (empty($validated['message']) && !$request->hasFile('attachments')) {
                return response()->json(['error' => 'Сообщение или вложение обязательно'], 422);
            }
            
            // Обработка вложений
            $attachmentsData = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('chat_attachments/' . $currentUser->id, 'public');
                    $attachmentsData[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'type' => $file->getMimeType()
                    ];
                }
            }
            
            // Создание сообщения
            $message = new Message();
            $message->sender_id = $currentUser->id;
            $message->receiver_id = $recipient->id;
            $message->content = $validated['message'] ?? null;
            $message->attachments = !empty($attachmentsData) ? json_encode($attachmentsData) : null;
            $message->save();
            
            // Обновляем last_seen_at для текущего пользователя, если поле существует
            if (Schema::hasColumn('users', 'last_seen_at')) {
                $currentUser->last_seen_at = now();
                $currentUser->save();
            }
            
            // Подготовка вложений для ответа
            $attachments = [];
            if (!empty($attachmentsData)) {
                foreach ($attachmentsData as $attachment) {
                    $attachments[] = [
                        'name' => $attachment['name'],
                        'url' => asset('storage/' . $attachment['path']),
                        'type' => $attachment['type']
                    ];
                }
            }
            
            return response()->json([
                'status' => 'success',
                'message' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'content' => $message->content,
                    'attachments' => $attachments,
                    'created_at' => $message->created_at,
                    'read_at' => null
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при отправке сообщения: ' . $e->getMessage());
            return response()->json(['error' => 'Произошла ошибка при отправке сообщения'], 500);
        }
    }

    public function getUnreadCount()
    {
        $count = Message::where('receiver_id', Auth::id()) // Изменено с recipient_id на receiver_id
            ->whereNull('read_at')
            ->count();
        
        return response()->json(['count' => $count]);
    }

    /**
     * Получение списка групповых чатов пользователя
     */
    public function getChatGroups()
    {
        try {
            $currentUser = Auth::user();
            $chatGroups = $currentUser->chatGroups()
                ->with('creator')
                ->withCount('users')
                ->get()
                ->map(function ($group) {
                    // Получение последнего сообщения в группе
                    $lastMessage = Message::where('chat_group_id', $group->id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    // Количество непрочитанных сообщений для текущего пользователя
                    $unreadCount = Message::where('chat_group_id', $group->id)
                        ->where('sender_id', '!=', Auth::id())
                        ->whereNull('read_at')
                        ->count();
                    
                    return [
                        'id' => $group->id,
                        'name' => $group->name,
                        'description' => $group->description,
                        'avatar' => $group->avatar ?: asset('storage/avatar/group_default.png'),
                        'isAdmin' => $group->isAdmin(Auth::id()),
                        'members' => $group->users_count,
                        'createdBy' => $group->creator->name,
                        'lastMessage' => $lastMessage ? Str::limit($lastMessage->content, 30) : null,
                        'unreadCount' => $unreadCount,
                        'lastActivity' => $lastMessage ? $this->formatLastActivity($lastMessage->created_at) : $this->formatLastActivity($group->created_at)
                    ];
                });
            
            return response()->json($chatGroups);
        } catch (\Exception $e) {
            \Log::error('Ошибка при получении групп: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Ошибка при получении групповых чатов'], 500);
        }
    }

    /**
     * Создание новой группы
     */
    public function createChatGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:2048',
            'members' => 'required|array|min:1',
            'members.*' => 'exists:users,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('chat_group_avatars', 'public');
            }
            
            $chatGroup = ChatGroup::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'avatar' => $avatarPath ? 'storage/' . $avatarPath : null,
                'created_by' => Auth::id(),
            ]);
            
            // Добавляем создателя как администратора
            $chatGroup->users()->attach(Auth::id(), ['role' => 'admin']);
            
            // Добавляем остальных участников
            $members = array_diff($validated['members'], [Auth::id()]);
            foreach ($members as $memberId) {
                $chatGroup->users()->attach($memberId, ['role' => 'member']);
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'group' => [
                    'id' => $chatGroup->id,
                    'name' => $chatGroup->name,
                    'description' => $chatGroup->description,
                    'avatar' => $chatGroup->avatar ?: asset('storage/avatar/group_default.png'),
                    'members' => count($validated['members']),
                    'isAdmin' => true,
                    'createdBy' => Auth::user()->name,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Ошибка при создании группы: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'Произошла ошибка при создании группового чата'], 500);
        }
    }

    /**
     * Получение сообщений группового чата
     */
    public function getGroupMessages($id)
    {
        $currentUser = Auth::user();
        
        try {
            $chatGroup = ChatGroup::findOrFail($id);
            
            // Проверяем, является ли пользователь участником группы
            if (!$chatGroup->isMember($currentUser->id)) {
                return response()->json([
                    'error' => 'У вас нет доступа к этой группе'
                ], 403);
            }
            
            // Получаем сообщения группы
            $messages = Message::where('chat_group_id', $chatGroup->id)
                ->orderBy('created_at')
                ->get()
                ->map(function ($message) {
                    // Подготовка вложений, если есть
                    $attachments = [];
                    if ($message->attachments) {
                        foreach (json_decode($message->attachments, true) as $attachment) {
                            $attachments[] = [
                                'name' => $attachment['name'],
                                'url' => asset('storage/' . $attachment['path']),
                                'type' => $attachment['type'] ?? 'file'
                            ];
                        }
                    }
                    
                    // Получаем данные отправителя
                    $sender = User::find($message->sender_id);
                    
                    return [
                        'id' => $message->id,
                        'sender_id' => $message->sender_id,
                        'sender_name' => $sender ? $sender->name : 'Неизвестный пользователь',
                        'sender_avatar' => $sender && $sender->avatar ? $sender->avatar : asset('storage/avatar/default.png'),
                        'content' => $message->content,
                        'attachments' => $attachments,
                        'created_at' => $message->created_at,
                        'read_at' => $message->read_at
                    ];
                });
            
            // Отмечаем сообщения как прочитанные
            Message::where('chat_group_id', $chatGroup->id)
                ->where('sender_id', '!=', $currentUser->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
            
            return response()->json([
                'messages' => $messages,
                'group' => [
                    'id' => $chatGroup->id,
                    'name' => $chatGroup->name,
                    'description' => $chatGroup->description,
                    'avatar' => $chatGroup->avatar ?: asset('storage/avatar/group_default.png'),
                    'isAdmin' => $chatGroup->isAdmin($currentUser->id),
                    'members' => $chatGroup->users()->count(),
                    'createdBy' => $chatGroup->creator->name,
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Группа не найдена'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Ошибка при получении сообщений группы: ' . $e->getMessage());
            return response()->json([
                'error' => 'Произошла ошибка при получении сообщений группы'
            ], 500);
        }
    }

    /**
     * Отправка сообщения в групповой чат
     */
    public function sendGroupMessage($id, Request $request)
    {
        try {
            $currentUser = Auth::user();
            $chatGroup = ChatGroup::findOrFail($id);
            
            // Проверяем, является ли пользователь участником группы
            if (!$chatGroup->isMember($currentUser->id)) {
                return response()->json([
                    'error' => 'У вас нет доступа к этой группе'
                ], 403);
            }
            
            $validated = $request->validate([
                'message' => 'nullable|string|max:2000',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|max:10240' // макс. 10MB
            ]);
            
            // Проверка наличия сообщения или вложений
            if (empty($validated['message']) && !$request->hasFile('attachments')) {
                return response()->json(['error' => 'Сообщение или вложение обязательно'], 422);
            }
            
            // Обработка вложений
            $attachmentsData = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('chat_attachments/' . $currentUser->id, 'public');
                    $attachmentsData[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'type' => $file->getMimeType()
                    ];
                }
            }
            
            // Создание сообщения
            $message = new Message();
            $message->sender_id = $currentUser->id;
            $message->chat_group_id = $chatGroup->id;
            $message->content = $validated['message'] ?? null;
            $message->attachments = !empty($attachmentsData) ? json_encode($attachmentsData) : null;
            $message->save();
            
            // Подготовка вложений для ответа
            $attachments = [];
            if (!empty($attachmentsData)) {
                foreach ($attachmentsData as $attachment) {
                    $attachments[] = [
                        'name' => $attachment['name'],
                        'url' => asset('storage/' . $attachment['path']),
                        'type' => $attachment['type']
                    ];
                }
            }
            
            return response()->json([
                'status' => 'success',
                'message' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $currentUser->name,
                    'sender_avatar' => $currentUser->avatar ?: asset('storage/avatar/default.png'),
                    'content' => $message->content,
                    'attachments' => $attachments,
                    'created_at' => $message->created_at,
                    'read_at' => null
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при отправке сообщения в группу: ' . $e->getMessage());
            return response()->json(['error' => 'Произошла ошибка при отправке сообщения'], 500);
        }
    }

    /**
     * Получение новых сообщений группового чата
     */
    public function getNewGroupMessages($id, Request $request)
    {
        $currentUser = Auth::user();
        $lastId = $request->input('last_id', 0);
        
        try {
            $chatGroup = ChatGroup::findOrFail($id);
            
            // Проверяем, является ли пользователь участником группы
            if (!$chatGroup->isMember($currentUser->id)) {
                return response()->json([
                    'error' => 'У вас нет доступа к этой группе'
                ], 403);
            }
            
            // Получаем только новые сообщения
            $messages = Message::where('chat_group_id', $chatGroup->id)
                ->where('id', '>', $lastId)
                ->orderBy('created_at')
                ->get()
                ->map(function ($message) {
                    // Подготовка вложений, если есть
                    $attachments = [];
                    if ($message->attachments) {
                        foreach (json_decode($message->attachments, true) as $attachment) {
                            $attachments[] = [
                                'name' => $attachment['name'],
                                'url' => asset('storage/' . $attachment['path']),
                                'type' => $attachment['type'] ?? 'file'
                            ];
                        }
                    }
                    
                    // Получаем данные отправителя
                    $sender = User::find($message->sender_id);
                    
                    return [
                        'id' => $message->id,
                        'sender_id' => $message->sender_id,
                        'sender_name' => $sender ? $sender->name : 'Неизвестный пользователь',
                        'sender_avatar' => $sender && $sender->avatar ? $sender->avatar : asset('storage/avatar/default.png'),
                        'content' => $message->content,
                        'attachments' => $attachments,
                        'created_at' => $message->created_at,
                        'read_at' => $message->read_at
                    ];
                });
            
            // Отмечаем сообщения как прочитанные только если они от других пользователей
            Message::where('chat_group_id', $chatGroup->id)
                ->where('sender_id', '!=', $currentUser->id)
                ->where('id', '>', $lastId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
            
            return response()->json([
                'messages' => $messages
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Группа не найдена'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Ошибка при получении новых сообщений группы: ' . $e->getMessage());
            return response()->json([
                'error' => 'Произошла ошибка при получении новых сообщений группы'
            ], 500);
        }
    }

    /**
     * Поиск по сообщениям
     */
    public function searchMessages(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|min:3',
            'chat_id' => 'nullable|integer|exists:users,id',
            'chat_group_id' => 'nullable|integer|exists:chat_groups,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);
        
        try {
            $currentUser = Auth::user();
            $query = $validated['query'];
            
            // Базовый запрос для поиска
            $messagesQuery = Message::where(function($q) use ($query) {
                $q->where('content', 'like', "%{$query}%");
            });
            
            // Фильтр по личным чатам
            if (isset($validated['chat_id'])) {
                $messagesQuery->where(function($q) use ($currentUser, $validated) {
                    $q->where(function($q1) use ($currentUser, $validated) {
                        $q1->where('sender_id', $currentUser->id)
                           ->where('receiver_id', $validated['chat_id']);
                    })->orWhere(function($q1) use ($currentUser, $validated) {
                        $q1->where('sender_id', $validated['chat_id'])
                           ->where('receiver_id', $currentUser->id);
                    });
                })->whereNull('chat_group_id');
            } 
            // Фильтр по групповым чатам
            elseif (isset($validated['chat_group_id'])) {
                // Проверяем доступ к группе
                $chatGroup = ChatGroup::findOrFail($validated['chat_group_id']);
                if (!$chatGroup->isMember($currentUser->id)) {
                    return response()->json([
                        'error' => 'У вас нет доступа к этой группе'
                    ], 403);
                }
                
                $messagesQuery->where('chat_group_id', $validated['chat_group_id']);
            }
            // Если не указан конкретный чат, ищем во всех чатах пользователя
            else {
                // Получаем ID всех групп пользователя
                $userGroupIds = $currentUser->chatGroups()->pluck('chat_groups.id')->toArray();
                
                $messagesQuery->where(function($q) use ($currentUser, $userGroupIds) {
                    $q->where(function($q1) use ($currentUser) {
                        $q1->where('sender_id', $currentUser->id)
                           ->orWhere('receiver_id', $currentUser->id);
                    })->orWhereIn('chat_group_id', $userGroupIds);
                });
            }
            
            // Фильтрация по дате
            if (isset($validated['from_date'])) {
                $messagesQuery->whereDate('created_at', '>=', $validated['from_date']);
            }
            
            if (isset($validated['to_date'])) {
                $messagesQuery->whereDate('created_at', '<=', $validated['to_date']);
            }
            
            // Получаем результаты
            $messages = $messagesQuery->orderBy('created_at', 'desc')->limit(100)->get();
            
            // Форматируем результаты
            $results = $messages->map(function($message) {
                $sender = User::find($message->sender_id);
                $receiver = $message->receiver_id ? User::find($message->receiver_id) : null;
                $chatGroup = $message->chat_group_id ? ChatGroup::find($message->chat_group_id) : null;
                
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'created_at' => $message->created_at,
                    'sender' => [
                        'id' => $sender->id,
                        'name' => $sender->name,
                        'avatar' => $sender->avatar ?: asset('storage/avatar/default.png')
                    ],
                    'is_group' => !is_null($message->chat_group_id),
                    'chat_details' => $message->chat_group_id 
                        ? [
                            'id' => $chatGroup->id,
                            'name' => $chatGroup->name,
                            'type' => 'group'
                          ]
                        : [
                            'id' => $receiver ? $receiver->id : null,
                            'name' => $receiver ? $receiver->name : 'Неизвестный пользователь',
                            'type' => 'private'
                          ]
                ];
            });
            
            return response()->json([
                'results' => $results,
                'total' => $results->count(),
                'query' => $query
            ]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при поиске сообщений: ' . $e->getMessage());
            return response()->json([
                'error' => 'Произошла ошибка при поиске сообщений'
            ], 500);
        }
    }

    // Получение деталей группового чата
    public function getChatGroup($id)
    {
        $currentUser = Auth::user();
        try {
            $chatGroup = \App\Models\ChatGroup::with('users')->findOrFail($id);
            if (!$chatGroup->isMember($currentUser->id)) {
                return response()->json(['error' => 'У вас нет доступа к этой группе'], 403);
            }
            return response()->json([
                'id'          => $chatGroup->id,
                'name'        => $chatGroup->name,
                'description' => $chatGroup->description,
                'avatar'      => $chatGroup->avatar ?: asset('storage/avatar/group_default.png'),
                'members'     => $chatGroup->users()->get(['id', 'name', 'avatar'])->toArray()
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Группа не найдена'], 404);
        }
    }

    // Обновление данных группового чата
    public function updateChatGroup($id, Request $request)
    {
        $currentUser = Auth::user();
        try {
            $chatGroup = \App\Models\ChatGroup::findOrFail($id);
            if (!$chatGroup->isAdmin($currentUser->id)) {
                return response()->json(['error' => 'Недостаточно прав'], 403);
            }
            $validated = $request->validate([
                'name'        => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'avatar'      => 'nullable|image|max:2048',
            ]);
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('chat_group_avatars', 'public');
                $chatGroup->avatar = 'storage/' . $avatarPath;
            }
            if (isset($validated['name'])) {
                $chatGroup->name = $validated['name'];
            }
            if (array_key_exists('description', $validated)) {
                $chatGroup->description = $validated['description'];
            }
            $chatGroup->save();
            return response()->json(['status' => 'success', 'group' => $chatGroup]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Группа не найдена'], 404);
        } catch (\Exception $e) {
            \Log::error('Ошибка обновления группы: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка при обновлении группы'], 500);
        }
    }

    // Удаление группового чата
    public function deleteChatGroup($id)
    {
        $currentUser = Auth::user();
        try {
            $chatGroup = \App\Models\ChatGroup::findOrFail($id);
            if (!$chatGroup->isAdmin($currentUser->id)) {
                return response()->json(['error' => 'Недостаточно прав'], 403);
            }
            $chatGroup->delete();
            return response()->json(['status' => 'success']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Группа не найдена'], 404);
        } catch (\Exception $e) {
            \Log::error('Ошибка удаления группы: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка при удалении группы'], 500);
        }
    }

    // Добавление пользователя в групповой чат
    public function addChatGroupUser($id, Request $request)
    {
        $currentUser = Auth::user();
        try {
            $chatGroup = \App\Models\ChatGroup::findOrFail($id);
            if (!$chatGroup->isAdmin($currentUser->id)) {
                return response()->json(['error' => 'Недостаточно прав'], 403);
            }
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);
            $userId = $validated['user_id'];
            if ($chatGroup->users()->where('user_id', $userId)->exists()) {
                return response()->json(['error' => 'Пользователь уже в группе'], 422);
            }
            $chatGroup->users()->attach($userId, ['is_admin' => false]);
            return response()->json(['status' => 'success']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Группа не найдена'], 404);
        } catch (\Exception $e) {
            \Log::error('Ошибка добавления пользователя в группу: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка при добавлении пользователя'], 500);
        }
    }

    // Удаление пользователя из группового чата
    public function removeChatGroupUser($id, $user_id)
    {
        $currentUser = Auth::user();
        try {
            $chatGroup = \App\Models\ChatGroup::findOrFail($id);
            if (!$chatGroup->isAdmin($currentUser->id)) {
                return response()->json(['error' => 'Недостаточно прав'], 403);
            }
            if (!$chatGroup->users()->where('user_id', $user_id)->exists()) {
                return response()->json(['error' => 'Пользователь не найден в группе'], 404);
            }
            $chatGroup->users()->detach($user_id);
            return response()->json(['status' => 'success']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Группа не найдена'], 404);
        } catch (\Exception $e) {
            \Log::error('Ошибка удаления пользователя из группы: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка при удалении пользователя'], 500);
        }
    }

    /**
     * Проверка наличия новых сообщений во всех чатах
     */
    public function checkNewMessagesInAllChats()
    {
        $currentUser = Auth::user();
        
        try {
            // Проверяем личные сообщения
            $newPersonalMessages = Message::where('receiver_id', $currentUser->id)
                ->whereNull('read_at')
                ->whereNull('chat_group_id')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Проверяем групповые сообщения в группах, где состоит пользователь
            $userGroups = $currentUser->chatGroups()->pluck('chat_groups.id')->toArray();
            
            $newGroupMessages = Message::whereIn('chat_group_id', $userGroups)
                ->where('sender_id', '!=', $currentUser->id)
                ->whereNull('read_at')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Отключаем уведомления для групповых чатов - используем только личные сообщения
            // для формирования уведомлений, игнорируя $newGroupMessages
            $allNewMessages = $newPersonalMessages->sortByDesc('created_at');
            
            $hasNewMessages = $allNewMessages->count() > 0;
            
            // Если есть новые сообщения в личных чатах, берем самое последнее для уведомления
            $lastMessage = null;
            if ($hasNewMessages) {
                $latestMessage = $allNewMessages->first();
                
                $sender = User::find($latestMessage->sender_id);
                $senderName = $sender ? $sender->name : 'Неизвестный пользователь';
                
                // Определяем ID чата (будет только ID контакта для личных сообщений)
                $chatId = $latestMessage->sender_id;
                
                $lastMessage = [
                    'id' => $latestMessage->id,
                    'chatId' => $chatId,
                    'isGroup' => false, // Всегда false, так как отображаем только личные сообщения
                    'senderName' => $senderName,
                    'content' => $latestMessage->content,
                    'createdAt' => $latestMessage->created_at
                ];
            }
            
            // Для счетчика в шапке сайта включаем и личные, и групповые сообщения
            $totalNewMessagesCount = $newPersonalMessages->count() + $newGroupMessages->count();
            
            return response()->json([
                'hasNewMessages' => $hasNewMessages,
                'newMessagesCount' => $totalNewMessagesCount,
                'lastMessage' => $lastMessage
            ]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при проверке новых сообщений: ' . $e->getMessage());
            return response()->json([
                'hasNewMessages' => false,
                'error' => 'Произошла ошибка при проверке новых сообщений'
            ], 500);
        }
    }

    /**
     * Проверка наличия новых сообщений в личных чатах
     */
    public function checkNewMessagesInChats()
    {
        return $this->checkNewMessagesInAllChats();
    }

    /**
     * Проверка наличия новых сообщений в групповых чатах
     */
    public function checkNewMessagesInGroups()
    {
        return $this->checkNewMessagesInAllChats();
    }
}
