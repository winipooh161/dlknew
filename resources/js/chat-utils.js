/**
 * Вспомогательные функции для чата
 */

import { attachMessageActionListeners } from './message-actions';

/**
 * Форматирование времени сообщений
 * @param {string|Date} timestamp - метка времени сообщения
 * @returns {string} - отформатированное время
 */
export function formatTime(timestamp) {
    const date = timestamp instanceof Date ? timestamp : new Date(timestamp);
    if (isNaN(date)) return '';
    
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');
    return `${hours}:${minutes}`;
}

/**
 * Экранирование HTML для предотвращения XSS
 * @param {string} text - исходный текст
 * @returns {string} - экранированный текст
 */
export function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Прокрутка контейнера сообщений вниз
 * @param {boolean} smooth - использовать плавную прокрутку
 */
export function scrollToBottom(smooth = true) {
    const chatMessages = document.getElementById('chat-messages');
    if (chatMessages) {
        chatMessages.scrollTo({
            top: chatMessages.scrollHeight,
            behavior: smooth ? 'smooth' : 'auto'
        });
    }
}

/**
 * Фильтрация сообщений по критерию
 * @param {boolean} showPinnedOnly - показывать только закрепленные
 */
export function filterMessages(showPinnedOnly = false) {
    const messages = document.querySelectorAll('#chat-messages .message');
    messages.forEach(message => {
        if (showPinnedOnly) {
            message.style.display = message.classList.contains('pinned') ? '' : 'none';
        } else {
            message.style.display = '';
        }
    });
}

/**
 * Форматирует размер файла в читаемый формат
 * @param {number} bytes - размер файла в байтах
 * @returns {string} - форматированный размер
 */
function formatFileSize(bytes) {
    if (bytes === 0 || !bytes) return '';
    const k = 1024;
    const sizes = ['Байт', 'КБ', 'МБ', 'ГБ'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Рендеринг списка сообщений
 * @param {Array} messages - массив сообщений
 * @param {number} currentUserId - ID текущего пользователя
 * @param {Set} loadedMessageIds - Set с ID уже загруженных сообщений
 * @param {string} csrfToken - CSRF-токен
 * @param {string} chatType - тип чата
 * @param {string} chatId - ID чата
 */
export function renderMessages(messages, currentUserId, loadedMessageIds, csrfToken, chatType, chatId) {
    if (!messages || messages.length === 0) return;
    
    const chatMessagesList = document.querySelector('#chat-messages ul');
    if (!chatMessagesList) return;
    
    let fragment = document.createDocumentFragment();
    
    messages.forEach(message => {
        if (loadedMessageIds.has(message.id)) return;
        
        loadedMessageIds.add(message.id);
        const isCurrentUser = message.sender_id == currentUserId;
        
        const messageEl = document.createElement('li');
        messageEl.className = `message ${isCurrentUser ? 'own' : ''}`;
        if (message.is_pinned) messageEl.classList.add('pinned');
        messageEl.setAttribute('data-id', message.id);
        
        const messageHeader = document.createElement('div');
        messageHeader.className = 'message-header';
        
        const senderName = document.createElement('span');
        senderName.className = 'sender-name';
        senderName.textContent = message.sender_name || 'Пользователь';
        
        const messageTime = document.createElement('span');
        messageTime.className = 'message-time';
        messageTime.textContent = formatTime(message.created_at);
        
        messageHeader.appendChild(senderName);
        messageHeader.appendChild(messageTime);
        
        const messageContent = document.createElement('div');
        messageContent.className = 'message-content';
        
        // Добавляем текст сообщения, если он есть
        if (message.message && message.message.trim()) {
            const textContent = document.createElement('div');
            textContent.className = 'message-text';
            textContent.innerHTML = escapeHtml(message.message).replace(/\n/g, '<br>');
            messageContent.appendChild(textContent);
        }
        
        // Добавляем вложения, если они есть
        if (message.attachments && message.attachments.length > 0) {
            const attachmentsContainer = document.createElement('div');
            attachmentsContainer.className = 'attachments';
            
            // Группируем изображения и файлы
            const images = [];
            const files = [];
            
            message.attachments.forEach(attachment => {
                if (!attachment) return;
                
                if (attachment.mime && attachment.mime.startsWith('image/')) {
                    images.push(attachment);
                } else {
                    files.push(attachment);
                }
            });
            
            // Отображаем изображения
            if (images.length > 0) {
                const imagesContainer = document.createElement('div');
                imagesContainer.className = `attachment-images images-${images.length > 3 ? 'grid' : 'row'}`;
                
                images.forEach((image) => {
                    const imgContainer = document.createElement('div');
                    imgContainer.className = 'image-container';
                    
                    const img = document.createElement('img');
                    img.src = image.url;
                    img.alt = image.original_file_name || 'Изображение';
                    img.className = 'attachment-image';
                    img.loading = 'lazy'; // Ленивая загрузка изображений
                    
                    // Увеличение изображения при клике
                    img.addEventListener('click', () => {
                        const modal = document.createElement('div');
                        modal.className = 'image-modal';
                        modal.innerHTML = `
                            <div class="image-modal-content">
                                <img src="${image.url}" alt="${image.original_file_name || 'Изображение'}">
                                <button class="close-modal">&times;</button>
                            </div>
                        `;
                        document.body.appendChild(modal);
                        modal.addEventListener('click', (e) => {
                            if (e.target === modal || e.target.classList.contains('close-modal')) {
                                modal.remove();
                            }
                        });
                    });
                    
                    imgContainer.appendChild(img);
                    imagesContainer.appendChild(imgContainer);
                });
                
                attachmentsContainer.appendChild(imagesContainer);
            }
            
            // Отображаем файлы
            if (files.length > 0) {
                const filesContainer = document.createElement('div');
                filesContainer.className = 'attachment-files';
                
                files.forEach(file => {
                    const fileLink = document.createElement('a');
                    fileLink.href = file.url;
                    fileLink.className = 'attachment-file';
                    fileLink.target = '_blank';
                    
                    // Определяем иконку в зависимости от типа файла
                    let fileIcon = '📄';
                    if (file.mime) {
                        if (file.mime.includes('pdf')) fileIcon = '📕';
                        else if (file.mime.includes('word')) fileIcon = '📘';
                        else if (file.mime.includes('excel') || file.mime.includes('spreadsheet')) fileIcon = '📊';
                        else if (file.mime.includes('zip') || file.mime.includes('rar')) fileIcon = '🗂️';
                        else if (file.mime.includes('audio')) fileIcon = '🎵';
                        else if (file.mime.includes('video')) fileIcon = '🎬';
                    }
                    
                    const fileName = file.original_file_name || 'Файл';
                    fileLink.innerHTML = `<span class="file-icon">${fileIcon}</span> ${fileName}`;
                    
                    // Добавляем размер файла, если доступен
                    if (file.size) {
                        const fileSizeElement = document.createElement('span');
                        fileSizeElement.className = 'file-size';
                        fileSizeElement.textContent = formatFileSize(file.size);
                        fileLink.appendChild(fileSizeElement);
                    }
                    
                    filesContainer.appendChild(fileLink);
                });
                
                attachmentsContainer.appendChild(filesContainer);
            }
            
            messageContent.appendChild(attachmentsContainer);
        }
        
        // Добавляем кнопки действий для сообщений
        if (!message.is_system) {
            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'message-actions';
            
            // Кнопка удаления (только для своих сообщений)
            if (isCurrentUser) {
                const deleteBtn = document.createElement('button');
                deleteBtn.className = 'delete-message';
                deleteBtn.setAttribute('data-message-id', message.id);
                deleteBtn.innerHTML = `<img src="${window.deleteImgUrl || '/img/delete.svg'}" alt="Удалить">`;
                actionsDiv.appendChild(deleteBtn);
            }
            
            // Кнопки закрепления/открепления
            if (message.is_pinned) {
                const unpinBtn = document.createElement('button');
                unpinBtn.className = 'unpin-message';
                unpinBtn.setAttribute('data-message-id', message.id);
                unpinBtn.innerHTML = `<img src="${window.unpinImgUrl || '/img/unpin.svg'}" alt="Открепить">`;
                actionsDiv.appendChild(unpinBtn);
            } else {
                const pinBtn = document.createElement('button');
                pinBtn.className = 'pin-message';
                pinBtn.setAttribute('data-message-id', message.id);
                pinBtn.innerHTML = `<img src="${window.pinImgUrl || '/img/pin.svg'}" alt="Закрепить">`;
                actionsDiv.appendChild(pinBtn);
            }
            
            messageContent.appendChild(actionsDiv);
        }
        
        messageEl.appendChild(messageHeader);
        messageEl.appendChild(messageContent);
        
        fragment.appendChild(messageEl);
    });
    
    chatMessagesList.appendChild(fragment);
    scrollToBottom();
    
    // Навешиваем обработчики на кнопки действий
    attachMessageActionListeners(csrfToken, chatType, chatId);
}

/**
 * Функция для получения новых сообщений из API
 * Вызывается периодически для проверки новых сообщений
 */
export async function fetchNewMessages() {
    if (!window.currentChatId || !window.currentChatType) return;
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        // Добавляем уникальный параметр времени и сброс кеша
        const timestamp = new Date().getTime();
        
        const response = await fetch(`/chats/${window.currentChatType}/${window.currentChatId}/new-messages?t=${timestamp}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            },
            body: JSON.stringify({
                last_message_id: window.lastLoadedMessageId || 0
            })
        });
        
        if (response.ok) {
            const data = await response.json();
           
            
            if (data.messages && data.messages.length > 0) {
                // Убедимся, что loadedMessageIds существует
                if (!window.loadedMessageIds) {
                    window.loadedMessageIds = new Set();
                }
                
                // Обновим ID последнего сообщения до отрисовки
                let maxMessageId = window.lastLoadedMessageId || 0;
                data.messages.forEach(message => {
                    if (message.id > maxMessageId) {
                        maxMessageId = message.id;
                    }
                });
                window.lastLoadedMessageId = maxMessageId;
                
                // Рендер новых сообщений с проверками
                const chatMessagesList = document.querySelector('#chat-messages ul');
                if (!chatMessagesList) {
                    console.error('Контейнер для сообщений не найден!');
                    return;
                }
                
                renderMessages(
                    data.messages,
                    window.Laravel?.user?.id,
                    window.loadedMessageIds,
                    csrfToken,
                    window.currentChatType,
                    window.currentChatId
                );
                
                // Прокрутка вниз после добавления сообщений
                scrollToBottom();
                
                // Отмечаем сообщения как прочитанные
                markMessagesAsRead(window.currentChatId, window.currentChatType);
            }
        } else {
         
        }
    } catch (error) {
      
    }
}

/**
 * Отмечает сообщения в чате как прочитанные
 * @param {string|number} chatId ID чата
 * @param {string} chatType Тип чата (personal или group)
 */
export function markMessagesAsRead(chatId, chatType) {
    if (!chatId || !chatType) return;
    
    fetch(`/chats/${chatType}/${chatId}/mark-read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'Accept': 'application/json'
        }
    }).catch(error => console.error('Ошибка при пометке сообщений как прочитанных:', error));
}

/**
 * Инициализация WebSocket подключения для чата
 */
export function initChatWebSockets(chatId, chatType) {
    if (!window.Echo) {
        console.warn('Laravel Echo не инициализирован');
        return;
    }
    
    try {
        // Подписываемся на событие нового сообщения
        window.Echo.private(`chat.${chatType}.${chatId}`)
            .listen('.message.sent', (e) => {
                console.log('Новое сообщение WebSocket:', e);
                if (e.message && !document.querySelector(`.message[data-id="${e.message.id}"]`)) {
                    const messagesList = document.querySelector('#chat-messages ul');
                    if (messagesList) {
                        // Рендерим новое сообщение
                        const loadedIds = new Set();
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        renderMessages([e.message], window.Laravel.user.id, loadedIds, csrfToken, chatType, chatId);
                    }
                }
            })
            .listen('.message.deleted', (e) => {
                if (e.message_id) {
                    const messageEl = document.querySelector(`.message[data-id="${e.message_id}"]`);
                    if (messageEl) {
                        messageEl.remove();
                    }
                }
            });
            
        // Подписываемся на индикатор печатания
        window.Echo.private(`typing.${chatType}.${chatId}`)
            .listenForWhisper('typing', (e) => {
                const typingIndicator = document.getElementById('typing-indicator');
                if (typingIndicator) {
                    typingIndicator.textContent = `${e.name} печатает...`;
                    typingIndicator.style.display = 'block';
                    
                    // Скрываем индикатор через 3 секунды
                    setTimeout(() => {
                        typingIndicator.style.display = 'none';
                    }, 3000);
                }
            });
            
    } catch (error) {
        console.error('Ошибка при настройке WebSocket для чата:', error);
    }
}
