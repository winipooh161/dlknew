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

    function loadMessages(chatId, chatType) {
        currentChatId = chatId;
        currentChatType = chatType;
        const chatMessagesContainer = document.getElementById('chat-messages');
        const chatMessagesList = chatMessagesContainer.querySelector('ul');
        
        // Очищаем список сообщений
        chatMessagesList.innerHTML = '';
        
        // Сбрасываем lastLoadedMessageId при загрузке нового чата
        window.lastLoadedMessageId = 0;
        
        const chatItem = document.querySelector(`[data-chat-id="${chatId}"][data-chat-type="${chatType}"] h5`);
        const chatHeader = document.getElementById('chat-header');
        chatHeader.textContent = chatItem ? chatItem.textContent : 'Выберите чат для общения';

        fetch(`/chats/${chatType}/${chatId}/messages`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Очищаем список для предотвращения дублирования
                chatMessagesList.innerHTML = '';
                
                // Обновляем lastLoadedMessageId на основе полученных сообщений
                if (data.messages && data.messages.length > 0) {
                    const lastMsg = data.messages[data.messages.length - 1];
                    window.lastLoadedMessageId = lastMsg.id;
                }
                
                renderMessages(data.messages, currentUserId, new Set(), csrfToken, currentChatType, currentChatId);
                markMessagesAsRead(chatId, chatType);
                subscribeToChat(chatId, chatType);
            })
            .catch(error => {
                console.error('Ошибка загрузки сообщений:', error);
            });
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