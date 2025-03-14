<div class="chat-container">
    <!-- Список чатов -->
    <div class="chat-list">
        @foreach ($chats as $chat)
            <div class="chat-item {{ $currentChatId === $chat['id'] ? 'Активный' : '' }}"
                 wire:click="$emit('selectChat', {{ $chat['id'] }})">
                <img src="{{ $chat['avatar_url'] }}" alt="Avatar" class="avatar">
                <div class="chat-info">
                    <h4>{{ $chat['name'] }}</h4>
                    <p>{{ $chat['unread_count'] > 0 ? '(' . $chat['unread_count'] . ' новых)' : 'Нет новых сообщений' }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Окно сообщений -->
    <div class="chat-window">
        @if ($currentChatId)
            <div class="messages">
                @foreach ($messages as $message)
                    <div class="message {{ $message->sender_id === $userId ? 'sent' : 'received' }}">
                        <p>{{ $message->message }}</p>
                        <small>{{ $message->created_at->format('H:i') }}</small>
                    </div>
                @endforeach
            </div>

            <!-- Отправка нового сообщения -->
            <div class="send-message">
                <input type="text" wire:model="newMessage" placeholder="Введите сообщение...">
                <button wire:click="sendMessage">Отправить</button>
            </div>
        @else
            <div class="no-chat">
                <p>Выберите чат, чтобы начать общение.</p>
            </div>
        @endif
    </div>
</div>
