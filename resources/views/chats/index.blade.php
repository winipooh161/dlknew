<body>
    <!-- Передаем данные пользователя -->
    <script>
        window.Laravel = {
            user: @json([
                'id' => auth()->id(),
                'name' => auth()->user()->name ?? 'Anon',
            ]),
        };

        window.pinImgUrl = "{{ asset('storage/icon/pin.svg') }}";
        window.unpinImgUrl = "{{ asset('storage/icon/unpin.svg') }}";
        window.deleteImgUrl = "{{ asset('storage/icon/deleteMesg.svg') }}";
    </script>

    <!-- Регистрация Service Worker для Firebase Messaging -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/firebase-messaging-sw.js')
                .then(registration => {
                    console.log('Service Worker зарегистрирован:', registration.scope);
                })
                .catch(err => {
                    console.error('Ошибка регистрации Service Worker:', err);
                });
        }
    </script>

    <script>
        // Имитируем онлайн-статус (при реальной реализации можно получить данные через API)
        window.onlineStatus = function(userId) {
            // ...existing code, например, проверка через AJAX каждые N секунд...
            return true; // пример, что пользователь online
        };

        // Логика бесконечной прокрутки для сообщений
        let currentPage = 1;
        window.loadMoreMessages = function(chatType, chatId) {
            currentPage++;
            // AJAX-запрос для загрузки новых сообщений
            // ...existing code...
        };
    </script>
    <script>
        window.typingTimeout = null;
        window.isTyping = false;

        window.startTyping = function(chatType, chatId) {
            if (!window.isTyping) {
                window.isTyping = true;
                sendTypingEvent(chatType, chatId);
            }

            clearTimeout(window.typingTimeout);
            window.typingTimeout = setTimeout(function() {
                window.isTyping = false;
            }, 3000); // Отправляем событие, только если пользователь печатает
        };

        function sendTypingEvent(chatType, chatId) {
            fetch(`/chats/${chatType}/${chatId}/typing`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        console.error('Ошибка отправки события typing');
                    }
                })
                .catch(error => {
                    console.error('Ошибка сети:', error);
                });
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function(){
            setTimeout(function(){
                const urlParams = new URLSearchParams(window.location.search);
                const activeChatId = urlParams.get('active_chat');
                if(activeChatId) {
                    const chatItem = document.querySelector(`[data-chat-id="${activeChatId}"]`);
                    if(chatItem) {
                        chatItem.click();
                    }
                }
            }, 500);
        });
    </script>

    @if (isset($supportChat) && $supportChat)
        <!-- Чат технической поддержки -->
        <div class="chat-container support-chat">
            <div class="support-chat-block-skiter">
                <img src="{{ asset('img/support/support.png') }}" alt="Поддержка">
                <span>Время работы:</span> <br>
                <p>Пн-пт: 9:00-18:00</p>
            </div>
            <div class="chat-box">
                <div class="chat-header">

                    Техническая поддержка
                    <!-- Кнопка фильтра закреплённых сообщений -->
                    <button id="toggle-pinned" class="toggle-pinned" style="margin-left:10px;">Показать только
                        закрепленные</button>
                </div>
                <div class="chat-messages" id="chat-messages">
                    <ul></ul>
                </div>
                <div class="chat-input" style="position: relative;">
                    <textarea id="chat-message" placeholder="Введите сообщение..." maxlength="500"></textarea>
                    <input type="file" class="file-input" style="display: none;" multiple>
                    <button type="button" class="attach-file">
                        <img src="{{ asset('storage/icon/Icon__file.svg') }}" alt="Прикрепить файл" width="24"
                            height="24">
                    </button>
                    <button id="send-message">
                        <img src="{{ asset('storage/icon/send_mesg.svg') }}" alt="Отправить" width="24"
                            height="24">
                    </button>
                </div>
            </div>
        </div>
    @elseif(isset($dealChat))
        <div class="chat-container user__deals_chats"
            data-chat-id="{{ isset($dealChat) ? $dealChat->id : '' }}"
            data-chat-type="{{ isset($dealChat) ? $dealChat->type : '' }}">
            <div class="chat-box">
                <div class="chat-header">
                    {{ $dealChat->name }}
                </div>
                <div class="chat-messages" id="chat-messages">
                    <ul></ul>
                </div>
                <div class="chat-input" style="position: relative;">
                    <textarea id="chat-message" placeholder="Введите сообщение..." maxlength="500"></textarea>
                    <input type="file" class="file-input" style="display: none;" multiple>
                    <button type="button" class="attach-file">
                        <img src="{{ asset('storage/icon/Icon__file.svg') }}" alt="Прикрепить файл" width="24" height="24">
                    </button>
                    <button id="send-message">
                        <img src="{{ asset('storage/icon/send_mesg.svg') }}" alt="Отправить" width="24" height="24">
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="chat-container">
            <div class="user-list" id="chat-list-container">
                <h4>Все чаты</h4>
                <input type="text" id="search-chats" placeholder="Поиск по чатам и сообщениям..." />
                @if (auth()->user()->status == 'coordinator' || auth()->user()->status == 'admin')
                    <a href="{{ route('chats.group.create') }}" class="create__group">Создать групповой чат</a>
                @endif
                <ul id="chat-list">
                    @if (isset($chats) && count($chats))
                        @foreach ($chats as $chat)
                            <li data-chat-id="{{ $chat['id'] }}" data-chat-type="{{ $chat['type'] }}"
                                style="position: relative; display: flex; align-items: center; margin-bottom: 10px; cursor: pointer;">
                                <div class="user-list__avatar">
                                    @if($chat['type'] == 'group')
                                        @if(!empty($chat['avatar_url']))
                                            <img src="{{ asset($chat['avatar_url']) }}" alt="{{ $chat['name'] }}" width="40" height="40" loading="lazy">
                                        @else
                                            <img src="{{ asset('storage/avatars/group_default.png') }}" alt="{{ $chat['name'] }}" width="40" height="40" loading="lazy">
                                        @endif
                                    @else
                                        @if(!empty($chat['avatar_url']))
                                            <img src="{{ asset($chat['avatar_url']) }}" alt="{{ $chat['name'] }}" width="40" height="40" loading="lazy">
                                        @else
                                            <img src="{{ asset('storage/avatars/user_default.png') }}" alt="{{ $chat['name'] }}" width="40" height="40" loading="lazy">
                                        @endif
                                    @endif
                                </div>
                                <div class="user-list__info" style="margin-left: 10px; width: 100%;">
                                    <h5>
                                        {{ $chat['name'] }}
                                        @if ($chat['unread_count'] > 0)
                                            <span class="unread-count">{{ $chat['unread_count'] }}</span>
                                        @endif
                                    </h5>
                                </div>
                            </li>
                        @endforeach
                    @else
                        <p>Чатов пока нет</p>
                    @endif
                </ul>
                <div class="search-results" id="search-results" style="display: none;"></div>
            </div>
            <div class="chat-box">
                <div class="chat-header">

                    <span id="chat-header">Выберите чат для общения</span>

                    <!-- Кнопка фильтра для стандартного режима -->
                    <button id="toggle-pinned" class="toggle-pinned" style="margin-left:10px;">Показать только
                        закрепленные</button>
                </div>
                <div class="chat-messages" id="chat-messages">
                    <ul></ul>
                </div>
                <div class="chat-input" style="position: relative;">
                    <textarea id="chat-message" placeholder="Введите сообщение..." maxlength="500"></textarea>
                    <input type="file" class="file-input" style="display: none;" multiple>
                    <button type="button" class="attach-file">
                        <img src="{{ asset('storage/icon/Icon__file.svg') }}" alt="Прикрепить файл" width="24"
                            height="24">
                    </button>
                    <button id="send-message">
                        <img src="{{ asset('storage/icon/send_mesg.svg') }}" alt="Отправить" width="24"
                            height="24">
                    </button>
                </div>
            </div>
        </div>

    @endif

</body>

<style>
    .image-collage {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .collage-item {
        flex: 1 1 calc(33.333% - 10px);
        max-width: calc(33.333% - 10px);
    }

    .collage-item img {
        width: 100%;
        height: auto;
        border-radius: 4px;
    }

    .attachment-file {
        display: flex;
        align-items: center;
        padding: 8px;
        background-color: #f5f5f5;
        border-radius: 4px;
        margin: 5px 0;
    }

    .attachment-file a {
        margin-left: 10px;
        color: #007bff;
        text-decoration: none;
        word-break: break-all;
    }

    .attachment-icon {
        font-size: 20px;
    }
</style>
