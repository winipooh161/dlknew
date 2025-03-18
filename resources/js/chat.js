import { initializeEmojiPicker } from './emoji-picker';
import { attachMessageActionListeners } from './message-actions';
import { formatTime, escapeHtml, scrollToBottom, filterMessages, renderMessages, fetchNewMessages } from './chat-utils';
import { showChatNotification, checkForNewMessages } from './notification';

document.addEventListener('DOMContentLoaded', () => {
    // Общие переменные
    let chatContainer = document.querySelector('.chat-container') || document.getElementById('chat-container');
    if (!chatContainer || !chatContainer.dataset.chatId || !chatContainer.dataset.chatType) {
        console.warn("Chat container не найден или отсутствуют параметры чата, используются значения по умолчанию.");
        window.currentChatId = '0';
        window.currentChatType = 'group';
    } else {
        window.currentChatId = chatContainer.dataset.chatId;
        window.currentChatType = chatContainer.dataset.chatType;
    }
    const currentUserId = window.Laravel?.user?.id;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let loadedMessageIds = new Set();
    window.loadedMessageIds = loadedMessageIds;
    window.lastLoadedMessageId = 0; // Глобальная переменная для ID последнего сообщения

    // Делаем fetchNewMessages доступной глобально
    window.fetchNewMessages = fetchNewMessages;

    // Универсальная функция fetch с async/await
    async function fetchJSON(url, options = {}) {
        try {
            const response = await fetch(url, options);
            if (!response.ok) {
                const errText = await response.text();
                if (url.includes('build/manifest.json')) {
                    console.error('Vite manifest не найден. Проверьте, что вы выполнили сборку (npm run dev или npm run build).');
                }
                throw new Error(`HTTP ${response.status}: ${errText}`);
            }
            return await response.json();
        } catch (err) {
            console.error('Fetch error:', err);
            throw err;
        }
    }

    // Функция для подписки на обновления из Firestore
    function subscribeToFirebaseMessages(chatId) {
        if (!window.firestore) return;
        const messagesRef = window.firestore.collection
            ? window.firestore.collection(`chats/${chatId}/messages`)
            : null;
        if (!messagesRef) return;
        const q = messagesRef.orderBy('created_at');
        q.onSnapshot((snapshot) => {
            let newMessages = [];
            snapshot.docChanges().forEach(change => {
                if (change.type === 'added') {
                    const data = change.doc.data();
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

    // Функция загрузки сообщений
    async function loadMessages(chatId, chatType) {
        // Устанавливаем глобальные переменные для доступа в других частях приложения
        window.currentChatId = chatId;
        window.currentChatType = chatType;
        
        window.lastLoadedMessageId = 0;
        const chatMessagesContainer = document.getElementById('chat-messages');
        const chatMessagesList = chatMessagesContainer.querySelector('ul');
        chatMessagesList.innerHTML = '';
        loadedMessageIds.clear();
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
                    renderMessages(data.messages, window.Laravel.user.id, loadedMessageIds, csrfToken, chatType, chatId);
                }
                await markMessagesAsRead(chatId, chatType);
                subscribeToFirebaseMessages(chatId);
                // Запускаем проверку новых сообщений сразу после загрузки истории
                fetchNewMessages();
            }
        } catch (error) {
            showToast('Ошибка загрузки сообщений: ' + error.message);
            console.error('Ошибка загрузки сообщений:', error);
        }
    }

    // Обработчики для прикрепления файлов
    const setupFileAttachment = () => {
        const attachFileButtons = document.querySelectorAll('.attach-file');
        const fileInputs = document.querySelectorAll('.file-input');
        
        attachFileButtons.forEach((button, index) => {
            if (fileInputs[index]) {
                // Обработчик клика по кнопке прикрепления
                button.addEventListener('click', () => {
                    fileInputs[index].click(); // Активируем скрытый input
                });
                
                // При выборе файлов не выводим их имена и сразу отправляем сообщение
                fileInputs[index].addEventListener('change', (e) => {
                    const files = e.target.files;
                    if (files.length > 0) {
                        console.log('Выбраны файлы, отправляем сообщение');
                        sendMessage();
                    }
                });
            }
        });
    };

    // Вызываем функцию настройки прикрепления файлов
    setupFileAttachment();

    // Функция отправки сообщения
    async function sendMessage() {
        if (!currentChatId || (!chatMessageInput.value.trim() && !document.querySelector('.file-input').files.length)) return;
        
        sendMessageButton.disabled = true;
        const message = chatMessageInput.value.trim();
        const fileInput = document.querySelector('.file-input');
        const files = fileInput.files;
        const formData = new FormData();
        
        formData.append('message', message);
        
        // Добавляем файлы в formData
        if (files && files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                formData.append('attachments[]', files[i]);
            }
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
                renderMessages([data.message], data.message.sender_id, loadedMessageIds, csrfToken, currentChatType, currentChatId);
                window.lastLoadedMessageId = data.message.id;
                chatMessageInput.value = '';
                document.querySelector('.file-input').value = '';
                
                // Очищаем превью файлов после отправки
                const filePreview = document.querySelector('.file-preview');
                if (filePreview) {
                    filePreview.innerHTML = '';
                    filePreview.style.display = 'none';
                }
            } else {
                showToast(data.error || 'Ошибка при отправке сообщения');
            }
        } catch (e) {
            showToast('Ошибка при отправке сообщения: ' + e.message);
            console.error('Ошибка при отправке сообщения:', e);
        } finally {
            sendMessageButton.disabled = false;
        }
    }

    // Функция пометки сообщений как прочитанных
    function markMessagesAsRead(chatId, chatType) {
        fetch(`/chats/${chatType}/${chatId}/mark-read`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        }).catch(e => console.error('Ошибка при пометке сообщений как прочитанных:', e));
    }

    // Функция получения id последнего отображённого сообщения
    function getLastMessageId() {
        const lastMsg = document.querySelector('#chat-messages .message:last-child');
        return lastMsg ? parseInt(lastMsg.getAttribute('data-id')) : 0;
    }

    // Функция debounce
    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    // Функция отображения уведомлений
    function showToast(message) {
        // Минимальная реализация для устранения ошибки
        console.log('Toast message:', message);
    }

    // Обработчики событий
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
                    chatBox.classList.add('active');
                    userList.classList.add('inactive');
                    chatBox.classList.add('active');
                }
            }
        });
    }

    const sendMessageButton = document.getElementById('send-message');
    const chatMessageInput = document.getElementById('chat-message');
    
    // Инициализация emoji-picker для textarea
    initializeEmojiPicker(chatMessageInput);

    if (sendMessageButton) {
        sendMessageButton.addEventListener('click', sendMessage);
    }
    if (chatMessageInput) {
        chatMessageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        });
    }

    const togglePinnedBtn = document.getElementById('toggle-pinned');
    let showPinnedOnly = false;
    if (togglePinnedBtn) {
        togglePinnedBtn.addEventListener('click', function() {
            showPinnedOnly = !showPinnedOnly;
            // Вызываем фильтрацию с параметром true/false
            filterMessages(showPinnedOnly);
            // Меняем текст кнопки
            togglePinnedBtn.textContent = showPinnedOnly
                ? "Показать все сообщения"
                : "Показать только закрепленные";
        });
    }

    // Функция проверки и обновления сообщений с повышенной надежностью
    function setupChatUpdateInterval() {
        let chatUpdateTimer = setInterval(() => {
            try {
                if (window.fetchNewMessages && typeof window.fetchNewMessages === 'function') {
                    window.fetchNewMessages();
                }
            } catch (error) {
                console.error('Ошибка при обновлении чата:', error);
            }
        }, 1000);
        
        // Сохраняем таймер для возможной очистки в будущем
        window.chatUpdateTimer = chatUpdateTimer;
        
        // Запускаем первичное обновление
        if (window.fetchNewMessages) {
            window.fetchNewMessages();
        }
    }
    
    // Инициализируем интервал обновления
    setupChatUpdateInterval();
});

// Удаляем существующий setInterval в конце файла, так как его заменил setupChatUpdateInterval
// setInterval(() => {...}, 1000);