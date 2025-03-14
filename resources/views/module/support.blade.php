<div class="support wow fadeInLeft" data-wow-duration="1.5s" data-wow-delay="1.5s">
    <h1 class="flex">Техническая поддержка</h1>
    
    <!-- Добавляем блок для отображения ошибок -->
    <div id="error-messages" class="alert alert-danger" style="display: none;"></div>
    
    <div class="support__content">
        <div class="support__tickets">
            @if(isset($supportError))
                <div class="alert alert-warning">{{ $supportError }}</div>
            @else
                <!-- Чат с поддержкой -->
                <div class="chat-container support-chat">
                    <div class="support-chat-block-skiter">
                        <img src="{{ asset('img/support/support.png') }}" alt="Поддержка">
                        <span>Время работы:</span> <br>
                        <p>Пн-пт: 9:00-18:00</p>
                    </div>
                    <div class="chat-box active">
                        <div class="chat-header">
                            Техническая поддержка
                            <!-- Кнопка фильтра закреплённых сообщений -->
                            <button id="toggle-pinned" class="toggle-pinned">Показать только закрепленные</button>
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
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Инициализируем чат поддержки только если нет ошибки
            if (!document.querySelector('.alert-warning')) {
                const supportChatId = 55; // ID пользователя поддержки
                
                // Передаем информацию для JS
                window.Laravel = window.Laravel || {
                    user: @json([
                        'id' => auth()->id(),
                        'name' => auth()->user()->name ?? 'Пользователь'
                    ])
                };
                
                window.pinImgUrl = "{{ asset('storage/icon/pin.svg') }}";
                window.unpinImgUrl = "{{ asset('storage/icon/unpin.svg') }}";
                window.deleteImgUrl = "{{ asset('storage/icon/deleteMesg.svg') }}";
                
                // Инициализация чата с поддержкой
                initializeSupportChat(supportChatId);
            }
        });
        
        // Функция инициализации чата с поддержкой
        function initializeSupportChat(supportUserId) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const chatMessagesList = document.querySelector('#chat-messages ul');
            const chatMessageInput = document.getElementById('chat-message');
            const sendMessageButton = document.getElementById('send-message');
            const fileInput = document.querySelector('.file-input');
            const attachButton = document.querySelector('.attach-file');
            const togglePinnedButton = document.getElementById('toggle-pinned');
            let pinnedOnly = false;
            let loadedMessageIds = new Set();
            
            // Функция загрузки сообщений
            function loadMessages() {
                fetch(`/chats/personal/${supportUserId}/messages`)
                    .then(response => {
                        if (!response.ok) throw new Error('Ошибка загрузки сообщений');
                        return response.json();
                    })
                    .then(data => {
                        chatMessagesList.innerHTML = '';
                        loadedMessageIds.clear();
                        
                        if (data.messages && data.messages.length > 0) {
                            renderMessages(data.messages);
                            markMessagesAsRead();
                        }
                    })
                    .catch(err => {
                        console.error('Ошибка загрузки сообщений:', err);
                        document.getElementById('error-messages').textContent = 'Ошибка загрузки сообщений. Пожалуйста, попробуйте позже.';
                        document.getElementById('error-messages').style.display = 'block';
                    });
            }
            
            // Функция отправки сообщения
            function sendMessage() {
                if ((!chatMessageInput.value.trim() && !fileInput.files[0])) {
                    document.getElementById('error-messages').textContent = 'Сообщение не может быть пустым';
                    document.getElementById('error-messages').style.display = 'block';
                    setTimeout(() => {
                        document.getElementById('error-messages').style.display = 'none';
                    }, 3000);
                    return;
                }
                
                const formData = new FormData();
                formData.append('message', chatMessageInput.value.trim());
                
                for (let i = 0; i < fileInput.files.length; i++) {
                    formData.append('files[]', fileInput.files[i]);
                }
                
                fetch(`/chats/personal/${supportUserId}/messages`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error('Ошибка отправки сообщения');
                    return response.json();
                })
                .then(data => {
                    if (data.message) {
                        renderMessages([data.message]);
                        chatMessageInput.value = '';
                        fileInput.value = '';
                    }
                })
                .catch(err => {
                    console.error('Ошибка отправки сообщения:', err);
                    document.getElementById('error-messages').textContent = 'Ошибка отправки сообщения. Пожалуйста, попробуйте позже.';
                    document.getElementById('error-messages').style.display = 'block';
                });
            }
            
            // Функция рендеринга сообщений
            function renderMessages(messages) {
                const currentUserId = window.Laravel.user.id;
                messages.forEach(message => {
                    if (!loadedMessageIds.has(message.id)) {
                        const isMyMessage = (message.sender_id === currentUserId);
                        const liClass = message.message_type === 'notification' 
                            ? 'notification-message' 
                            : (isMyMessage ? 'my-message' : 'other-message');
                        const pinnedClass = message.is_pinned ? 'pinned' : '';
                        
                        let contentHtml = '';
                        if (message.message && message.message.trim() !== '') {
                            contentHtml += `<div>${escapeHtml(message.message)}</div>`;
                        }
                        
                        if (message.attachments && Array.isArray(message.attachments) && message.attachments.length > 0) {
                            message.attachments.forEach(attachment => {
                                if (attachment && attachment.mime && attachment.mime.startsWith('image/')) {
                                    contentHtml += `<div><img src="${attachment.url}" alt="Image" style="max-width:100%; border-radius:4px;"></div>`;
                                } else if (attachment && attachment.url) {
                                    contentHtml += `<div><a href="${attachment.url}" target="_blank">${escapeHtml(attachment.original_file_name || 'Файл')}</a></div>`;
                                }
                            });
                        }
                        
                        if(contentHtml.trim() === ''){
                            contentHtml = `<div style="color:#888;">[Пустое сообщение]</div>`;
                        }
                        
                        let actionsHtml = '';
                        if (isMyMessage) {
                            actionsHtml = `
                                <div class="message-controls">
                                    <button class="delete-message" data-id="${message.id}"><img src="${window.deleteImgUrl}" alt="Удалить"></button>
                                    ${message.is_pinned 
                                        ? `<button class="unpin-message" data-id="${message.id}"><img src="${window.unpinImgUrl}" alt="Открепить"></button>`
                                        : `<button class="pin-message" data-id="${message.id}"><img src="${window.pinImgUrl}" alt="Закрепить"></button>`
                                    }
                                </div>
                            `;
                        }
                        
                        const messageHtml = `
                            <li class="${liClass} ${pinnedClass}" data-id="${message.id}">
                                <div><strong>${isMyMessage ? 'Вы' : (message.sender_name || 'Поддержка')}</strong></div>
                                ${contentHtml}
                                ${actionsHtml}
                                <span class="message-time">${formatTime(message.created_at)}</span>
                            </li>
                        `;
                        
                        chatMessagesList.insertAdjacentHTML('beforeend', messageHtml);
                        loadedMessageIds.add(message.id);
                    }
                });
                
                // Прокрутка вниз после добавления сообщений
                chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
                
                // Применение фильтра закрепленных сообщений при необходимости
                if (pinnedOnly) {
                    filterPinnedMessages();
                }
                
                // Добавляем обработчики для кнопок действий
                attachMessageActionListeners();
            }
            
            // Функция пометки сообщений как прочитанных
            function markMessagesAsRead() {
                fetch(`/chats/personal/${supportUserId}/mark-read`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                }).catch(e => console.error('Ошибка при пометке сообщений как прочитанных:', e));
            }
            
            // Функция форматирования времени
            function formatTime(timestamp) {
                const date = new Date(timestamp);
                return date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
            }
            
            // Функция экранирования HTML
            function escapeHtml(text) {
                const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
                return (text || '').replace(/[&<>"']/g, m => map[m]);
            }
            
            // Функция фильтрации закрепленных сообщений
            function filterPinnedMessages() {
                document.querySelectorAll('#chat-messages ul li').forEach(li => {
                    li.style.display = pinnedOnly ? (li.classList.contains('pinned') ? '' : 'none') : '';
                });
            }
            
            // Функция добавления обработчиков для действий с сообщениями
            function attachMessageActionListeners() {
                // Обработчики для кнопок удаления сообщений
                document.querySelectorAll('.delete-message').forEach(button => {
                    button.onclick = function() {
                        const messageId = this.getAttribute('data-id');
                        if (confirm('Удалить сообщение?')) {
                            fetch(`/chats/personal/${supportUserId}/messages/${messageId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    this.closest('li').remove();
                                } else {
                                    alert(data.error || 'Ошибка удаления сообщения');
                                }
                            })
                            .catch(error => console.error('Ошибка:', error));
                        }
                    };
                });
                
                // Обработчики для кнопок закрепления сообщений
                document.querySelectorAll('.pin-message').forEach(button => {
                    button.onclick = function() {
                        const messageId = this.getAttribute('data-id');
                        fetch(`/chats/personal/${supportUserId}/messages/${messageId}/pin`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.innerHTML = `<img src="${window.unpinImgUrl}" alt="Открепить">`;
                                this.classList.remove('pin-message');
                                this.classList.add('unpin-message');
                                let li = this.closest('li');
                                li.classList.add('pinned');
                                filterPinnedMessages();
                                loadMessages(); // Перезагружаем сообщения для отображения уведомления
                            } else {
                                alert(data.error || 'Ошибка закрепления сообщения');
                            }
                        })
                        .catch(error => console.error('Ошибка:', error));
                    };
                });
                
                // Обработчики для кнопок открепления сообщений
                document.querySelectorAll('.unpin-message').forEach(button => {
                    button.onclick = function() {
                        const messageId = this.getAttribute('data-id');
                        fetch(`/chats/personal/${supportUserId}/messages/${messageId}/unpin`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.innerHTML = `<img src="${window.pinImgUrl}" alt="Закрепить">`;
                                this.classList.remove('unpin-message');
                                this.classList.add('pin-message');
                                let li = this.closest('li');
                                li.classList.remove('pinned');
                                filterPinnedMessages();
                                loadMessages(); // Перезагружаем сообщения для отображения уведомления
                            } else {
                                alert(data.error || 'Ошибка открепления сообщения');
                            }
                        })
                        .catch(error => console.error('Ошибка:', error));
                    };
                });
            }
            
            // Инициализация эмодзи-пикера
            function initializeEmojiPicker(textarea) {
                const container = textarea.parentElement;
                const emojiButton = document.createElement('button');
                const emojiPicker = document.createElement('div');
                
                emojiButton.textContent = "😉";
                emojiButton.type = "button";
                emojiButton.classList.add('emoji-button');
                
                emojiPicker.classList.add('emoji-picker');
                emojiPicker.style.position = 'absolute';
                emojiPicker.style.bottom = '50px';
                emojiPicker.style.left = '10px';
                
                const emojis = [
                    "😀","😁","😂","🤣","😃","😄","😅","😆","😉","😊","😍","😘","😜","😎","😭","😡",
                    "😇","😈","🙃","🤔","😥","😓","🤩","🥳","🤯","🤬","🤡","👻","💀","👽","🤖","🎃",
                    "🐱","🐶","🐭","🐹","🐰","🦊","🐻","🐼","🦁","🐮","🐷","🐸","🐵","🐔","🐧","🐦",
                    "🌹","🌻","🌺","🌷","🌼","🍎","🍓","🍒","🍇","🍉","🍋","🍊","🍌","🥝","🍍","🥭"
                ];
                
                let emojiHTML = '';
                emojis.forEach(emoji => {
                    emojiHTML += `<span class="emoji-item">${emoji}</span>`;
                });
                
                emojiPicker.innerHTML = emojiHTML;
                
                emojiPicker.addEventListener('click', (e) => {
                    if (e.target.classList.contains('emoji-item')) {
                        const emoji = e.target.textContent;
                        const cursorPos = textarea.selectionStart;
                        const textBefore = textarea.value.substring(0, cursorPos);
                        const textAfter = textarea.value.substring(cursorPos);
                        
                        textarea.value = textBefore + emoji + textAfter;
                        const newPos = cursorPos + emoji.length;
                        textarea.selectionStart = newPos;
                        textarea.selectionEnd = newPos;
                        textarea.focus();
                    }
                });
                
                container.appendChild(emojiButton);
                container.appendChild(emojiPicker);
                
                emojiPicker.style.display = "none";
                
                emojiButton.addEventListener('click', (event) => {
                    event.stopPropagation();
                    emojiPicker.style.display = (emojiPicker.style.display === "none") ? "flex" : "none";
                });
                
                document.addEventListener('click', (event) => {
                    if (!emojiPicker.contains(event.target) && !emojiButton.contains(event.target)) {
                        emojiPicker.style.display = "none";
                    }
                });
            }
            
            // Проверка новых сообщений
            function checkNewMessages() {
                const lastMessageEl = chatMessagesList.querySelector('li:last-child');
                const lastMessageId = lastMessageEl ? parseInt(lastMessageEl.getAttribute('data-id')) : 0;
                
                fetch(`/chats/personal/${supportUserId}/new-messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ last_message_id: lastMessageId })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.messages && data.messages.length > 0) {
                        renderMessages(data.messages);
                        markMessagesAsRead();
                    }
                })
                .catch(error => console.error('Ошибка при получении новых сообщений:', error));
            }
            
            // Получаем контейнер сообщений для прокрутки
            const chatMessagesContainer = document.getElementById('chat-messages');
            
            // Инициализируем эмодзи-пикер для поля ввода
            initializeEmojiPicker(chatMessageInput);
            
            // Отправка сообщения по клику на кнопку
            sendMessageButton.addEventListener('click', sendMessage);
            
            // Отправка сообщения по нажатию Enter (без Shift)
            chatMessageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
            
            // Прикрепление файлов
            attachButton.addEventListener('click', () => {
                fileInput.click();
            });
            
            // Автоматическая отправка при выборе файла
            fileInput.addEventListener('change', () => {
                if (fileInput.files.length > 0) {
                    sendMessage();
                }
            });
            
            // Переключение режима отображения закрепленных сообщений
            if (togglePinnedButton) {
                togglePinnedButton.addEventListener('click', () => {
                    pinnedOnly = !pinnedOnly;
                    togglePinnedButton.textContent = pinnedOnly ? 'Показать все сообщения' : 'Показать только закрепленные';
                    filterPinnedMessages();
                });
            }
            
            // Загружаем сообщения при инициализации
            loadMessages();
            
            // Устанавливаем интервал для проверки новых сообщений
            setInterval(checkNewMessages, 3000);
        }
    </script>

    <h1>Часто задаваемые вопросы</h1>
    <div class="faq__body support-faq__body">
        <div class="faq_block">
            <div class="faq_item">
                <div class="faq_question" onclick="toggleFaq(this)">
                    <span>Как создать бриф для проекта?</span>
                    <svg class="arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M7 10l5 5 5-5z"></path>
                    </svg>
                </div>
                <div class="faq_answer">
                    <p>Для создания брифа перейдите в раздел "Брифы" в вашем личном кабинете. Здесь доступны два типа брифов: общий и коммерческий.</p>
                </div>
            </div>
            <div class="faq_item">
                <div class="faq_question" onclick="toggleFaq(this)">
                    <span>Что происходит после заполнения брифа?</span>
                    <svg class="arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M7 10l5 5 5-5z"></path>
                    </svg>
                </div>
                <div class="faq_answer">
                    <p>После заполнения брифа становится доступна функция сделок для начала работы над проектом, а ответственные лица ведут контроль этапов.</p>
                </div>
            </div>
            <!-- Дополнительные FAQ -->
            <div class="faq_item">
                <div class="faq_question" onclick="toggleFaq(this)">
                    <span>Как начать сделку в системе?</span>
                    <svg class="arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M7 10l5 5 5-5z"></path>
                    </svg>
                </div>
                <div class="faq_answer">
                    <p>После создания сделки в личном кабинете ответственные могут отслеживать её ход и взаимодействовать через систему.</p>
                </div>
            </div>
            <div class="faq_item">
                <div class="faq_question" onclick="toggleFaq(this)">
                    <span>Как использовать групповый чат для сделки?</span>
                    <svg class="arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M7 10l5 5 5-5z"></path>
                    </svg>
                </div>
                <div class="faq_answer">
                    <p>Открывая сделку, вам доступен групповой чат для обсуждения деталей проекта с ответственными и заинтересованными сторонами.</p>
                </div>
            </div>
            <div class="faq_item">
                <div class="faq_question" onclick="toggleFaq(this)">
                    <span>Как работают уведомления и напоминания?</span>
                    <svg class="arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M7 10l5 5 5-5z"></path>
                    </svg>
                </div>
                <div class="faq_answer">
                    <p>Уведомления информируют вас об изменениях статусов сделок, задачах и запланированных действиях, чтобы ничего не упустить.</p>
                </div>
            </div>
            <div class="faq_item">
                <div class="faq_question" onclick="toggleFaq(this)">
                    <span>Что делать, если не пришло уведомление?</span>
                    <svg class="arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M7 10l5 5 5-5z"></path>
                    </svg>
                </div>
                <div class="faq_answer">
                    <p>Проверьте настройки уведомлений в личном кабинете и папку спам в почте. Если проблема сохраняется – свяжитесь с технической поддержкой.</p>
                </div>
            </div>
        </div>
    </div>
    <script>
        function toggleFaq(element) {
            const faqItem = element.parentElement;
            document.querySelectorAll('.faq_item').forEach(item => {
                if (item !== faqItem) item.classList.remove('active');
            });
            faqItem.classList.toggle('active');
        }
    </script>
</div>

