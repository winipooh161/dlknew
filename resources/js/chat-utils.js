import { attachMessageActionListeners } from './message-actions';

export function formatTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
}

export function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
}

export function scrollToBottom() {
    const chatMessagesContainer = document.getElementById('chat-messages');
    chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
}

export function filterMessages(pinnedOnly) {
    document.querySelectorAll('#chat-messages ul li').forEach(li => {
        li.style.display = pinnedOnly ? (li.classList.contains('pinned') ? '' : 'none') : '';
    });
}

export function renderMessages(messages, currentUserId, loadedMessageIds, csrfToken, currentChatType, currentChatId) {
    const chatMessagesContainer = document.getElementById('chat-messages');
    if (!chatMessagesContainer) {
        console.error('Элемент chat-messages не найден!');
        return;
    }
    
    const chatMessagesList = chatMessagesContainer.querySelector('ul');
    if (!chatMessagesList) {
        console.error('Список сообщений не найден в chat-messages!');
        return;
    }
    
    let html = '';
    let newMessagesAdded = false;
    
    messages.forEach(message => {
        // Проверяем, что сообщение еще не было загружено
        if (!loadedMessageIds.has(message.id)) {
            newMessagesAdded = true;
            if (message.message_type === 'notification' || message.is_system) {
                html += `
                    <li class="system-notification" data-id="${message.id}">
                        ${message.message}
                        <span class="message-time">${formatTime(message.created_at)}</span>
                    </li>
                `;
            } else {
                const isMyMessage = (message.sender_id === currentUserId);
                const liClass = message.message_type === 'notification' 
                    ? 'notification-message' 
                    : (isMyMessage ? 'my-message' : 'other-message');
                const pinnedClass = message.is_pinned ? 'pinned' : '';
                let readStatus = '';
                if (isMyMessage && message.is_read) {
                    readStatus = '<span class="read-status">✓✓</span>';
                }
                let contentHtml = '';
                if (message.message && message.message.trim() !== '') {
                    if (message.message_type === 'notification') {
                        contentHtml += message.message;
                    } else {
                        contentHtml += `<div>${escapeHtml(message.message)}</div>`;
                    }
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
                            <button class="delete-message" data-id="${message.id}"><img src="${deleteImgUrl}" alt="Удалить"></button>
                            ${message.is_pinned 
                                ? `<button class="unpin-message" data-id="${message.id}"><img src="${unpinImgUrl}" alt="Открепить"></button>`
                                : `<button class="pin-message" data-id="${message.id}"><img src="${pinImgUrl}" alt="Закрепить"></button>`
                            }
                        </div>
                    `;
                }
                html += `
                    <li class="${liClass} ${pinnedClass}" data-id="${message.id}">
                        <div><strong>${isMyMessage ? 'Вы' : escapeHtml(message.sender_name || 'Неизвестно')}</strong></div>
                        ${contentHtml}
                        ${actionsHtml}
                        <span class="message-time">${formatTime(message.created_at)}</span>
                        ${readStatus}
                    </li>
                `;
            }
            loadedMessageIds.add(message.id);
        }
    });
    
    if (html && newMessagesAdded) {
        chatMessagesList.insertAdjacentHTML('beforeend', html);
        scrollToBottom();
        
        // Используем импортированную функцию
        attachMessageActionListeners(csrfToken, currentChatType, currentChatId);
        
        filterMessages();
    }
}
