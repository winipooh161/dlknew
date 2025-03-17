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
                <div class="chat-messages" id="chat-messages" data-chat-type="support" data-chat-id="{{ $supportChat->id ?? 'null' }}">
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
                <div class="chat-messages" id="chat-messages" data-chat-type="deal" data-chat-id="{{ $dealChat->id ?? 'null' }}">
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
        <div class="chat-container" data-chat-id="{{ $activeChatId ?? '1' }}" data-chat-type="{{ $activeChatType ?? 'group' }}">
            <div class="user-list" id="chat-list-container">
                <h4>Все чаты</h4>
                <input type="text" id="search-chats" placeholder="Поиск по чатам и сообщениям..." />
                {{-- @if (auth()->user()->status == 'coordinator' || auth()->user()->status == 'admin')
                    <a href="{{ route('chats.group.create') }}" class="create__group">Создать групповой чат</a>
                @endif --}}
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
                    <span id="chat-header">Мой личный чат</span>
                    <div class="burger-users">
                        <span></span> <span></span> <span></span>
                    </div>
                    <!-- Кнопка фильтра для стандартного режима -->
                    <button id="toggle-pinned" class="toggle-pinned" style="margin-left:10px;">Показать только
                        закрепленные</button>
                </div>
                <div class="chat-messages" id="chat-messages" data-chat-type="group" data-chat-id="{{ $activeChatId ?? '1' }}">
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

    @if (isset($supportChat) && $supportChat)
        <div class="chat-container support-chat">
            <div class="chat-box">
                <div class="chat-messages" id="chat-messages" data-chat-type="support" data-chat-id="{{ $supportChat->id ?? 'null' }}">
                    <ul></ul>
                </div>
            </div>
        </div>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            import('{{ Vite::asset("resources/js/chat-utils.js") }}').then(module => {
                window.fetchNewMessages = module.fetchNewMessages;
                setInterval(window.fetchNewMessages, 1000);
                window.fetchNewMessages();
            }).catch(err => {
                console.error('Ошибка при импорте chat-utils.js:', err);
            });
        });
    </script>
      <!-- Стили для вложений и предпросмотра файлов -->
      <style>
      
    </style>
   <script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.innerWidth < 768) { // Код выполняется только если ширина экрана меньше 768px
            const burger = document.querySelector('.burger-users');
            const userList = document.querySelector('.user-list');
            if (burger && userList) {
                burger.addEventListener('click', () => {
                    if(userList.style.transform === 'translateX(0%)') {
                        userList.style.transform = 'translateX(-100%)';
                    } else {
                        userList.style.transform = 'translateX(0%)';
                    }
                });
            }
            // При выборе чата скрываем меню
            const chatItems = document.querySelectorAll('.user-list li');
            chatItems.forEach(item => {
                item.addEventListener('click', () => {
                    if(userList) {
                        userList.style.transform = 'translateX(-100%)';
                    }
                });
            });
        }
    });
    </script>
    <script>
    // Фильтрация чатов по введенному запросу
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('search-chats');
        const chatItems = document.querySelectorAll('#chat-list li');
        if(searchInput) {
            searchInput.addEventListener('input', () => {
                const query = searchInput.value.trim().toLowerCase();
                chatItems.forEach(item => {
                    const nameElement = item.querySelector('.user-list__info h5');
                    const chatName = nameElement ? nameElement.textContent.toLowerCase() : '';
                    item.style.display = chatName.includes(query) ? '' : 'none';
                });
            });
        }
    });
    </script>
    <style>
       
    </style>
</body>
