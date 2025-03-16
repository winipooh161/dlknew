<?php

namespace App\Http\Livewire;

use App\Models\Chat as ChatModel;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Chat extends Component
{
    public $chats = [];
    public $messages = [];
    public $currentChatId = null;
    public $newMessage = '';
    public $userId;

    protected $listeners = [
        'selectChat' => 'loadChat',
        'messageSent' => 'refreshMessages',
    ];

    public function mount()
    {
        $this->userId = Auth::id();
        $this->loadChats();
    }

    public function loadChats()
    {
        $this->chats = app('App\Http\Controllers\ChatController')->getUserChats($this->userId);
    }

    public function loadChat($chatId)
    {
        $this->currentChatId = $chatId;
        $this->messages = Message::where('chat_id', $chatId)
            ->orWhere(function ($query) {
                $query->where('sender_id', $this->userId)
                    ->orWhere('receiver_id', $this->userId);
            })
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function sendMessage()
    {
        if (!$this->newMessage) {
            return;
        }

        Message::create([
            'chat_id' => $this->currentChatId,
            'sender_id' => $this->userId,
            'message' => $this->newMessage,
        ]);

        $this->newMessage = '';
        $this->refreshMessages();
        $this->emit('messageSent');
    }

    public function refreshMessages()
    {
        if ($this->currentChatId) {
            $this->messages = Message::where('chat_id', $this->currentChatId)
                ->orderBy('created_at', 'asc')
                ->get();
            // Улучшение 105: Уведомляем фронтенд о необходимости автоскролла
            $this->emit('scrollToBottom');
        }
    }

    public function render()
    {
        return view('livewire.chat');
    }
}
