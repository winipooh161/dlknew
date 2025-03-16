import { initializeEmojiPicker } from './emoji-picker';
import { attachMessageActionListeners } from './message-actions';
import { formatTime, escapeHtml, scrollToBottom, filterMessages, renderMessages } from './chat-utils';
import { showChatNotification, checkForNewMessages } from './notification';

document.addEventListener('DOMContentLoaded', () => {
    // Глобальная переменная для хранения ID последнего загруженного сообщения
    window.lastLoadedMessageId = 0;
    
    let currentChatId = null;
    let currentChatType = null;
    const currentUserId = window.Laravel.user.id;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function markMessagesAsRead(chatId, chatType) {
        fetch(`/chats/${chatType}/${chatId}/mark-read`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        }).catch(e => console.error('Ошибка при пометке сообщений как прочитанных:', e));
    }

    function subscribeToChat(chatId, chatType) {
        // Пустая функция для совместимости
    }

    // Улучшенный универсальный уведомлятор (улучшение 12, 20)
    function showToast(message, type = 'error') {
        // Здесь можно подключить toaster с кастомными настройками, например, уведомлениям также можно добавить код ошибки (улучшения 12, 85)
        console.log(`[${type.toUpperCase()}] ${message}`);
    }

    async function loadMessages(chatId, chatType) {
        window.lastLoadedMessageId = 0;
        const chatMessagesContainer = document.getElementById('chat-messages');
        const chatMessagesList = chatMessagesContainer.querySelector('ul');
        chatMessagesList.innerHTML = '';
        const chatItem = document.querySelector(`[data-chat-id="${chatId}"][data-chat-type="${chatType}"] h5`);
        document.getElementById('chat-header').textContent = chatItem ? chatItem.textContent : 'Выберите чат для общения';
        try {
            const response = await fetch(`/chats/${chatType}/${chatId}/messages`);
            if (!response.ok) throw new Error('Ошибка запроса сообщений.');
            const data = await response.json();
            if (data.error) {
                showToast(data.error);
            } else {
                if (data.messages && data.messages.length > 0) {
                    window.lastLoadedMessageId = data.messages[data.messages.length - 1].id;
                }
                // Передаем дополнительные данные для улучшенной отрисовки (улучшение 38, 40)
                renderMessages(data.messages, window.Laravel.user.id, new Set(), csrfToken, chatType, chatId);
            }
            await markMessagesAsRead(chatId, chatType);
        } catch (error) {
            showToast('Ошибка загрузки сообщений: ' + error.message);
            console.error('Ошибка загрузки сообщений:', error);
        }
    }

    async function sendMessage() {
        if (!currentChatId || (!chatMessageInput.value.trim() && !document.querySelector('.file-input').files[0])) return;
        const message = chatMessageInput.value.trim();
        const fileInput = document.querySelector('.file-input');
        const files = fileInput.files;
        const formData = new FormData();
        formData.append('message', message);
        // Изменено: ключ заменён с "attachments[]" на "attachments"
        for (let i = 0; i < files.length; i++) {
            formData.append('attachments', files[i]);
        }
        try {
            const r = await fetch(`/chats/${currentChatType}/${currentChatId}/messages`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData,
            });
            if (!r.ok) {
                const errText = await r.text();
                throw new Error(errText);
            }
            const data = await r.json();
            if (data.message) {
                renderMessages([data.message], data.message.sender_id, new Set(), csrfToken, currentChatType, currentChatId);
                chatMessageInput.value = '';
                document.querySelector('.file-input').value = '';
            } else {
                showToast(data.error || 'Ошибка при отправке сообщения');
            }
        } catch (e) {
            showToast('Ошибка при отправке сообщения: ' + e.message);
            console.error('Ошибка при отправке сообщения:', e);
        }
    }

    // Создаем бургер-меню только для мобильной версии
    if (window.innerWidth <= 768) {
        const burgerMenu = document.createElement('div');
        burgerMenu.className = 'burger-menu';
        burgerMenu.innerHTML = `
            <div class="burger-icon">
                <span></span>
                <span></span>
                <span></span>
            </div>
        `;
        
        const chatHeader = document.querySelector('.chat-header');
        if (chatHeader) {
            chatHeader.appendChild(burgerMenu);

            const userList = document.querySelector('.user-list');
            const chatBox = document.querySelector('.chat-box');

            if (userList && chatBox) {
                burgerMenu.addEventListener('click', () => {
                    userList.classList.toggle('active');
                    chatBox.classList.toggle('inactive');
                });
            }
        }
    }

    const chatList = document.getElementById('chat-list');
    if (chatList) {
        chatList.addEventListener('click', (event) => {
            const chatElement = event.target.closest('li');
            if (!chatElement) return;
            const chatId = chatElement.getAttribute('data-chat-id');
            const chatType = chatElement.getAttribute('data-chat-type');
            if (currentChatId === chatId && currentChatType === chatType) return;
            loadMessages(chatId, chatType);

            // Закрываем бургер-меню при выборе чата на мобильной версии
            if (window.innerWidth <= 768) {
                const userList = document.querySelector('.user-list');
                const chatBox = document.querySelector('.chat-box');
                
                if (userList && chatBox) {
                    userList.classList.remove('active');
                    chatBox.classList.remove('inactive');
                    
                    // Добавляем классы inactive и active соответственно
                    userList.classList.add('inactive');
                    chatBox.classList.add('active');
                }
            }
        });
    }

    // При необходимости фильтрации сообщений
    function filterPinnedMessages() {
        if (document.getElementById('toggle-pinned')) {
            const isPinnedOnly = document.getElementById('toggle-pinned').textContent.includes('Показать все');
            filterMessages(isPinnedOnly);
        }
    }
});