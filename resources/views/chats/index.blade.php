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
                                            <img src="{{ asset('storage/avatars/group_default.svg') }}" alt="{{ $chat['name'] }}" width="40" height="40" loading="lazy">
                                        @endif
                                    @else
                                        @if(!empty($chat['avatar_url']))
                                            <img src="{{ asset($chat['avatar_url']) }}" alt="{{ $chat['name'] }}" width="40" height="40" loading="lazy">
                                        @else
                                            <img src="{{ asset('storage/avatars/group_default.svg') }}" alt="{{ $chat['name'] }}" width="40" height="40" loading="lazy">
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
                
                // Добавляем функцию для обновления непрочитанных сообщений
                window.updateUnreadCounts = function() {
                    fetch('/chats/unread-counts')
                        .then(response => response.json())
                        .then(data => {
                            // Обновляем счетчики непрочитанных сообщений для всех чатов
                            const chatItems = document.querySelectorAll('#chat-list li');
                            chatItems.forEach(item => {
                                const chatId = item.getAttribute('data-chat-id');
                                const chatType = item.getAttribute('data-chat-type');
                                
                                // Проверяем есть ли данные для этого чата
                                if (data[chatType] && data[chatType][chatId]) {
                                    const unreadCount = data[chatType][chatId];
                                    let countElement = item.querySelector('.unread-count');
                                    
                                    if (unreadCount > 0) {
                                        // Если есть непрочитанные сообщения
                                        if (!countElement) {
                                            // Создаем элемент, если его нет
                                            countElement = document.createElement('span');
                                            countElement.className = 'unread-count';
                                            const nameElement = item.querySelector('.user-list__info h5');
                                            if (nameElement) {
                                                nameElement.appendChild(countElement);
                                            }
                                        }
                                        countElement.textContent = unreadCount;
                                    } else if (countElement) {
                                        // Если нет непрочитанных сообщений, удаляем счетчик
                                        countElement.remove();
                                    }
                                }
                            });
                        })
                       
                };
                
                // Выполняем обе функции с интервалом в 1 секунду
                const updateAll = function() {
                    window.fetchNewMessages();
                    window.updateUnreadCounts();
                };
                
                // Запускаем сразу и устанавливаем интервал
                updateAll();
                setInterval(updateAll, 1000);
            }).catch(err => {
                console.error('Ошибка при импорте chat-utils.js:', err);
            });
        });
    </script>

    <!-- Добавляем новый скрипт для обработки клика по чату -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Находим все элементы чатов в списке
            const chatItems = document.querySelectorAll('#chat-list li');
            
            // Добавляем обработчик события клика на каждый элемент
            chatItems.forEach(item => {
                item.addEventListener('click', function() {
                    const chatId = this.getAttribute('data-chat-id');
                    const chatType = this.getAttribute('data-chat-type');
                    
                    if (!chatId || !chatType) return;
                    
                    // Отправляем запрос на маркировку сообщений как прочитанных
                    fetch(`/chats/${chatType}/${chatId}/mark-read`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            // Если запрос успешный, удаляем счетчик непрочитанных сообщений
                            const unreadCountElement = this.querySelector('.unread-count');
                            if (unreadCountElement) {
                                unreadCountElement.remove();
                            }
                        }
                        return response.json();
                    })
                    .catch(error => {
                        console.error('Ошибка при маркировке сообщений как прочитанных:', error);
                    });
                });
            });
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
    /* Стили для бургер-меню */
    .burger-users {
        display: none; /* Скрыто по умолчанию */
        cursor: pointer;
        z-index: 1000;
        position: relative;
    }
    
    .burger-users span {
        display: block;
        width: 25px;
        height: 3px;
        background-color: #333;
        margin: 5px 0;
    }
    
    /* Показываем бургер только на мобильных */
    @media (max-width: 768px) {
        .burger-users {
            display: block;
        }
        
        .user-list {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            position: fixed;
            left: 0;
            top: 0;
            height: 100%;
            width: 80%;
            z-index: 999;
            background-color: white;
            box-shadow: 2px 0 5px rgba(0,0,0,0.2);
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM загружен, инициализация бургер-меню');
    
    // Функция для настройки бургер-меню в зависимости от размера экрана
    function setupBurgerMenu() {
        const burger = document.querySelector('.burger-users');
        const userList = document.querySelector('.user-list');
        
        // Очищаем предыдущие обработчики, если они были
        if (burger) {
            burger.replaceWith(burger.cloneNode(true));
        }
        
        // Получаем обновленные элементы после клонирования
        const updatedBurger = document.querySelector('.burger-users');
        
        if (updatedBurger && userList) {
            // Работаем только на мобильных устройствах
            if (window.innerWidth < 768) {
                console.log('Настройка бургер-меню для мобильных устройств');
                
                // Устанавливаем начальное состояние
                userList.style.transform = 'translateX(-100%)';
                
                // Добавляем обработчик клика
                updatedBurger.addEventListener('click', () => {
                    console.log('Клик по бургер-меню (мобильный)');
                    
                    if(userList.style.transform === 'translateX(0%)') {
                        console.log('Скрываем меню');
                        userList.style.transform = 'translateX(-100%)';
                    } else {
                        console.log('Показываем меню');
                        userList.style.transform = 'translateX(0%)';
                    }
                });
                
                // При выборе чата скрываем меню только на мобильных устройствах
                const chatItems = document.querySelectorAll('#chat-list li');
                chatItems.forEach(item => {
                    item.addEventListener('click', () => {
                        // Проверяем ширину экрана перед скрытием списка
                        if (window.innerWidth < 768) {
                            userList.style.transform = 'translateX(-100%)';
                        }
                    });
                });
            } else {
                // На десктопах сбрасываем стили
                console.log('Десктоп: сбрасываем стили для бургер-меню');
                userList.style.transform = '';
            }
        } else {
            console.error('Элементы бургер-меню не найдены');
        }
    }
    
    // Начальная настройка
    setupBurgerMenu();
    
    // Слушаем изменения размера окна
    window.addEventListener('resize', () => {
        setupBurgerMenu();
    });
});
</script>

<!-- Исправляем основной обработчик клика на элементы чата -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Находим все элементы чатов в списке
        const chatItems = document.querySelectorAll('#chat-list li');
        const userList = document.querySelector('.user-list');
        
        // Добавляем обработчик события клика на каждый элемент
        chatItems.forEach(item => {
            item.addEventListener('click', function() {
                const chatId = this.getAttribute('data-chat-id');
                const chatType = this.getAttribute('data-chat-type');
                
                if (!chatId || !chatType) return;
                
                // Отправляем запрос на маркировку сообщений как прочитанных
                fetch(`/chats/${chatType}/${chatId}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (response.ok) {
                        // Если запрос успешный, удаляем счетчик непрочитанных сообщений
                        const unreadCountElement = this.querySelector('.unread-count');
                        if (unreadCountElement) {
                            unreadCountElement.remove();
                        }
                    }
                    return response.json();
                })
                .catch(error => {
                    console.error('Ошибка при маркировке сообщений как прочитанных:', error);
                });
                
                // Скрываем меню только на мобильных устройствах
                if (window.innerWidth < 768 && userList) {
                    userList.style.transform = 'translateX(-100%)';
                }
            });
        });
    });
</script>
</body>
