import { initializeEmojiPicker } from './emoji-picker';
import { attachMessageActionListeners } from './message-actions';
import { formatTime, escapeHtml, scrollToBottom, filterMessages, renderMessages } from './chat-utils';
import { showChatNotification, checkForNewMessages } from './notification';

document.addEventListener('DOMContentLoaded', () => {
    const chatContainer = document.querySelector('.chat-container');
    // Получаем параметры из data-атрибутов
    // Изменили с const на let для возможности переназначения в loadMessages
    let currentChatId = chatContainer ? chatContainer.dataset.chatId : null;
    let currentChatType = chatContainer ? chatContainer.dataset.chatType : null;
    
    if (!currentChatId || !currentChatType) {
        console.error("Неверные параметры чата: currentChatId или currentChatType не установлены");
    }

    const currentUserId = window.Laravel.user.id;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const pinImgUrl = window.pinImgUrl;
    const unpinImgUrl = window.unpinImgUrl;
    const deleteImgUrl = window.deleteImgUrl;
    let loadedMessageIds = new Set();
    let pinnedOnly = false;
    let notifiedChats = new Set();

    function showChatBox() {
        document.querySelector('.user-list').classList.remove('active');
        document.querySelector('.chat-box').classList.add('active');
    }

    // Новый универсальный fetch с async/await
    async function fetchJSON(url, options = {}) {
        try {
            const response = await fetch(url, options);
            if (!response.ok) {
                const errText = await response.text();
                throw new Error(`HTTP ${response.status}: ${errText}`);
            }
            return await response.json();
        } catch (err) {
            console.error('Fetch error:', err);
            throw err;
        }
    }

    // Добавляем функцию подписки на обновления из Firestore
    function subscribeToFirebaseMessages(chatId) {
        if (!window.firestore) return;
        // Используем динамический путь для коллекции сообщений выбранного чата
        const messagesRef = window.firestore.collection 
            ? window.firestore.collection(`chats/${chatId}/messages`) 
            : null;
        if (!messagesRef) {
            console.warn('Firestore коллекция не доступна');
            return;
        }
        // Создаём запрос на сортировку по created_at (предполагается, что такое поле хранится)
        const q = messagesRef.orderBy('created_at');
        q.onSnapshot((snapshot) => {
            let newMessages = [];
            snapshot.docChanges().forEach(change => {
                if (change.type === 'added') {
                    const data = change.doc.data();
                    // Предполагается, что data имеет формат, схожий с API методом renderMessages
                    if (!loadedMessageIds.has(data.id)) {
                        newMessages.push(data);
                    }
                }
            });
            if (newMessages.length > 0) {
                renderMessages(newMessages, window.Laravel.user.id, loadedMessageIds, csrfToken, currentChatType, currentChatId);
                markMessagesAsRead(currentChatId, currentChatType);
            }
        }, error => {
            console.error('Ошибка подписки на Firestore обновления:', error);
        });
    }

    // Обновляем глобальную переменную lastLoadedMessageId при загрузке сообщений
    function loadMessages(chatId, chatType) {
        currentChatId = chatId;
        currentChatType = chatType;
        const chatMessagesContainer = document.getElementById('chat-messages');
        const chatMessagesList = chatMessagesContainer.querySelector('ul');
        chatMessagesList.innerHTML = '';
        loadedMessageIds.clear();
        const chatItem = document.querySelector(`[data-chat-id="${chatId}"][data-chat-type="${chatType}"] h5`);
        document.getElementById('chat-header').textContent = chatItem ? chatItem.textContent : 'Выберите чат для общения';

        fetchJSON(`/chats/${chatType}/${chatId}/messages`)
            .then(data => {
                if (data.messages && data.messages.length > 0) {
                    window.lastLoadedMessageId = data.messages[data.messages.length - 1].id;
                }
                renderMessages(data.messages, currentUserId, loadedMessageIds, csrfToken, currentChatType, currentChatId);
                markMessagesAsRead(chatId, chatType);
                subscribeToChat(chatId, chatType);
                // Подписываемся на Firestore обновления для резервного режима и оперативного отображения истории
                subscribeToFirebaseMessages(chatId);
            })
            .catch(error => console.error('Ошибка загрузки сообщений:', error));
    }

    function sendMessage() {
        if (!currentChatId || (!chatMessageInput.value.trim() && !document.querySelector('.file-input').files[0])) return;
        const message = chatMessageInput.value.trim();
        const fileInput = document.querySelector('.file-input');
        const files = fileInput.files;
        let formData = new FormData();
        formData.append('message', message);
        // Изменено: ключ заменён с "attachments[]" на "attachments"
        for (let i = 0; i < files.length; i++) {
            formData.append('attachments', files[i]);
        }
        fetch(`/chats/${currentChatType}/${currentChatId}/messages`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData,
        })
        .then(r => {
            if (!r.ok) {
                return r.text().then(text => { throw new Error(text); });
            }
            return r.json();
        })
        .then(data => {
            if (data.message) {
                renderMessages([data.message], data.message.sender_id, loadedMessageIds, csrfToken, currentChatType, currentChatId);
                chatMessageInput.value = '';
                document.querySelector('.file-input').value = '';
            }
        })
        .catch(e => {
            console.error('Ошибка при отправке сообщения:', e);
            alert('Ошибка при отправке сообщения: ' + e.message); // Выводим сообщение об ошибке пользователю
        });
    }

    function markMessagesAsRead(chatId, chatType) {
        fetch(`/chats/${chatType}/${chatId}/mark-read`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        }).catch(e => console.error('Ошибка при пометке сообщений как прочитанных:', e));
    }

    // Добавляем функцию для подписки на чат с использованием Laravel Echo
    function subscribeToChat(chatId, chatType) {
        if (window.Echo) {
            window.Echo.channel(`chat.${chatType}.${chatId}`)
                .listen('MessageSent', event => {
                    console.log('Новое сообщение через веб-сокет:', event);
                    // Добавляем полученное сообщение, если его ещё нет
                    if (!loadedMessageIds.has(event.message.id)) {
                        renderMessages([event.message], currentUserId, loadedMessageIds, csrfToken, currentChatType, currentChatId);
                        markMessagesAsRead(chatId, chatType);
                    }
                });
            // Обработка ошибок соединения и автоматическое переподключение
            window.Echo.connector.socket.on('error', error => {
                console.error('WebSocket Error:', error);
            });
            window.Echo.connector.socket.on('reconnect_attempt', () => {
                console.log('Попытка переподключения к WebSocket...');
            });
        }
        // ...existing code or fallback...
    }

    // Изменяем функцию периодической проверки новых сообщений
    setInterval(() => {
        if (currentChatId && currentChatType) {
            const chatMessagesContainer = document.getElementById('chat-messages');
            if (!chatMessagesContainer) return;
            
            const chatMessagesList = chatMessagesContainer.querySelector('ul');
            if (!chatMessagesList) return;
            
            // Используем lastLoadedMessageId вместо поиска последнего элемента
            const lastMessageId = window.lastLoadedMessageId || 0;
            
            fetch(`/chats/${currentChatType}/${currentChatId}/new-messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json', // добавлено для возврата JSON
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    last_message_id: lastMessageId
                }),
            })
            .then(r => {
                const contentType = r.headers.get('content-type');
                if (contentType && contentType.indexOf('application/json') !== -1) {
                    return r.json();
                }
                return r.text().then(text => { throw new Error(text); });
            })
            .then(data => {
                if (data.messages && data.messages.length > 0) {
                    // Обновляем lastLoadedMessageId на основе новых сообщений
                    const lastMsg = data.messages[data.messages.length - 1];
                    window.lastLoadedMessageId = lastMsg.id;
                    
                    renderMessages(data.messages, data.current_user_id, loadedMessageIds, csrfToken, currentChatType, currentChatId);
                    markMessagesAsRead(currentChatId, currentChatType);
                }
            })
            .catch(e => {
                console.error('Ошибка при получении новых сообщений:', e);
            });
        }
    }, 2000); // Проверка новых сообщений каждые 2 секунды

    // Периодический запрос для обновления статуса доставки (delivered)
    setInterval(() => {
        if (currentChatId && currentChatType) {
            fetch(`/chats/${currentChatType}/${currentChatId}/mark-delivered`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
            }).catch(e => console.error('Ошибка при обновлении статуса доставки:', e));
        }
    }, 5000);

    // Функция debounce
    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    // Кэширование DOM-элементов example:
    const chatListElem = document.getElementById('chat-list');
    if(chatListElem){
        chatListElem.addEventListener('click', event => {
            const li = event.target.closest('li');
            if (!li) return;
            const chatId = li.getAttribute('data-chat-id');
            const chatType = li.getAttribute('data-chat-type');
            if(currentChatId === chatId && currentChatType === chatType) return;
            loadMessages(chatId, chatType);
        });
    }

    const searchInput = document.getElementById('search-chats');
    const searchResults = document.getElementById('search-results');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            const query = searchInput.value.trim().toLowerCase();
            if (query === '') {
                searchResults.style.display = 'none';
                Array.from(chatList.children).forEach(chat => { chat.style.display = 'flex'; });
            } else {
                Array.from(chatList.children).forEach(chat => {
                    const chatName = chat.querySelector('h5').textContent.toLowerCase();
                    chat.style.display = chatName.includes(query) ? 'flex' : 'none';
                });
                fetch(`/chats/search`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ query: query })
                })
                .then(response => response.json())
                .then(data => {
                    let resultsHTML = '';
                    if (data.chats && data.chats.length > 0) {
                        resultsHTML += '<h5>Чаты</h5><ul>';
                        data.chats.forEach(chat => {
                            resultsHTML += `<li data-chat-id="${chat.id}" data-chat-type="${chat.type}">${chat.name}</li>`;
                        });
                        resultsHTML += '</ul>';
                    }
                    if (data.messages && data.messages.length > 0) {
                        resultsHTML += '<h5>Сообщения</h5><ul>';
                        data.messages.forEach(msg => {
                            let chatId = msg.chat_id;
                            let chatType = "group";
                            if (!chatId) {
                                chatType = "personal";
                                chatId = (msg.sender_id == currentUserId ? msg.receiver_id : msg.sender_id);
                            }
                            resultsHTML += `<li data-chat-id="${chatId}" data-chat-type="${chatType}" data-message-id="${msg.id}">
                                <strong>${msg.sender_name}:</strong> ${msg.message.substring(0, 50)}...
                                <br><small>${formatTime(msg.created_at)}</small>
                            </li>`;
                        });
                        resultsHTML += '</ul>';
                    }
                    searchResults.innerHTML = resultsHTML;
                    searchResults.style.display = resultsHTML.trim() === '' ? 'none' : 'block';
                    Array.from(searchResults.querySelectorAll('li')).forEach(item => {
                        item.addEventListener('click', function() {
                            const chatId = this.getAttribute('data-chat-id');
                            const chatType = this.getAttribute('data-chat-type');
                            const messageId = this.getAttribute('data-message-id');
                            loadMessages(chatId, chatType);
                            searchInput.value = '';
                            searchResults.style.display = 'none';
                            if (messageId) {
                                setTimeout(() => {
                                    // Здесь можно реализовать выделение сообщения
                                }, 1000);
                            }
                        });
                    });
                })
                .catch(e => console.error('Ошибка поиска:', e));
            }
        }, 300));
    }

    function attachFileListener() {
        const attachButton = document.querySelector('.attach-file');
        const fileInput = document.querySelector('.file-input');
        if (attachButton && fileInput) {
            attachButton.addEventListener('click', () => { fileInput.click(); });
            fileInput.addEventListener('change', () => {
                if (fileInput.files.length > 0) { sendMessage(); }
            });
        }
    }

    if (document.readyState !== 'loading') { attachFileListener(); }
    else { document.addEventListener('DOMContentLoaded', attachFileListener); }

    const sendMessageButton = document.getElementById('send-message');
    const chatMessageInput = document.getElementById('chat-message');
    if (sendMessageButton) {
        sendMessageButton.addEventListener('click', sendMessage);
    }
    if (chatMessageInput) {
        chatMessageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        });
    }

    initializeEmojiPicker(chatMessageInput);

    const firstChat = document.getElementById('chat-list')?.querySelector('li');
    if (firstChat) {
        firstChat.click();
    }

    const togglePinnedButton = document.getElementById('toggle-pinned');
    if (togglePinnedButton) {
        togglePinnedButton.addEventListener('click', () => {
            pinnedOnly = !pinnedOnly;
            togglePinnedButton.textContent = pinnedOnly ? 'Показать все сообщения' : 'Показать только закрепленные';
            filterMessages(pinnedOnly);  // Передаем параметр pinnedOnly
        });
    }

    checkForNewMessages();

    // Убираем старый обработчик скролла
    // ...existing scroll event listener... 

    // Добавляем бесконечную прокрутку через IntersectionObserver
    const chatMessagesContainer = document.getElementById('chat-messages');
    if (chatMessagesContainer) {
        const sentinel = document.createElement('div');
        sentinel.id = 'scroll-sentinel';
        chatMessagesContainer.appendChild(sentinel);
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const currentPage = parseInt(chatMessagesContainer.getAttribute('data-page') || '1');
                    if (currentChatId && currentChatType) {
                        fetchJSON(`/chats/${currentChatType}/${currentChatId}/messages?page=${currentPage + 1}`)
                            .then(data => {
                                if (data.messages && data.messages.length > 0) {
                                    renderMessages(data.messages, currentUserId, loadedMessageIds, csrfToken, currentChatType, currentChatId);
                                    chatMessagesContainer.setAttribute('data-page', currentPage + 1);
                                }
                            })
                            .catch(err => console.error('Ошибка подгрузки старых сообщений:', err));
                    } else {
                        console.warn('Неверные параметры чата: currentChatId или currentChatType не установлены');
                    }
                }
            });
        }, {
            root: chatMessagesContainer,
            threshold: 1.0
        });
        observer.observe(sentinel);
    }

    function pinMessage(messageId) {
        fetch(`/chats/${currentChatType}/${currentChatId}/messages/${messageId}/pin`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken 
            },
            body: JSON.stringify({ })
        })
        .then(r => {
            if (!r.ok) {
                return r.text().then(text => { throw new Error(text); });
            }
            return r.json();
        })
        .then(data => {
            // ...обработка успешного закрепления...
        })
        .catch(e => console.error('Ошибка при закреплении сообщения:', e));
    }

    function unpinMessage(messageId) {
        fetch(`/chats/${currentChatType}/${currentChatId}/messages/${messageId}/unpin`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        }).catch(e => console.error('Ошибка при откреплении сообщения:', e));
    }

});
