import { filterMessages } from './chat-utils';
import { subscribeToNotifications } from './notification';
import { fetchNewMessages } from './chat-utils';

// Улучшение 103: Функция централизованного логирования ошибок
function logError(error) {
    if (window.Sentry) {
        window.Sentry.captureException(error);
    } else {
        console.error(error);
    }
}

export function attachMessageActionListeners(csrfToken, currentChatType, currentChatId) {
    // Обработчик для кнопок удаления сообщений
    document.querySelectorAll('.delete-message').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const messageId = this.getAttribute('data-message-id');
            if (!messageId) return;
            
            if (confirm('Вы уверены, что хотите удалить это сообщение?')) {
                try {
                    const response = await fetch(`/chats/${currentChatType}/${currentChatId}/messages/${messageId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        const messageElement = document.querySelector(`.message[data-id="${messageId}"]`);
                        if (messageElement) {
                            messageElement.remove();
                        }
                    } else {
                        throw new Error(data.error || 'Неизвестная ошибка');
                    }
                } catch (error) {
                    console.error('Ошибка при удалении сообщения:', error);
                    alert('Не удалось удалить сообщение: ' + error.message);
                }
            }
        });
    });
    
    // Обработчик для кнопок закрепления сообщений
    document.querySelectorAll('.pin-message').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const messageId = this.getAttribute('data-message-id');
            if (!messageId) return;
            
            // Оптимистично меняем иконку сразу: pin -> unpin
            const pinButton = this;
            const parent = pinButton.parentNode;
            const tempUnpinButton = document.createElement('button');
            tempUnpinButton.classList.add('unpin-message');
            tempUnpinButton.setAttribute('data-message-id', messageId);
            tempUnpinButton.innerHTML = `<img src="/img/icon/unpin.svg" alt="Открепить">`;
            parent.replaceChild(tempUnpinButton, pinButton);
            attachUnpinHandler(tempUnpinButton, csrfToken, currentChatType, currentChatId);
            
            try {
                const response = await fetch(`/chats/${currentChatType}/${currentChatId}/messages/${messageId}/pin`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (!data.message) {
                    throw new Error(data.error || 'Неизвестная ошибка');
                }
                // Уведомляем об успехе (иконка уже изменена)
                if (typeof showToast === 'function') {
                    showToast("Сообщение закреплено");
                } else {
                    console.log("Сообщение закреплено");
                }
            } catch (error) {
                console.error('Ошибка при закреплении сообщения:', error);
                alert('Не удалось закрепить сообщение: ' + error.message);
                // В случае ошибки возвращаем прежнюю иконку: unpin -> pin
                const unpinButton = document.querySelector(`.unpin-message[data-message-id="${messageId}"]`);
                if (unpinButton) {
                    const revertPinButton = document.createElement('button');
                    revertPinButton.classList.add('pin-message');
                    revertPinButton.setAttribute('data-message-id', messageId);
                    revertPinButton.innerHTML = `<img src="/img/icon/pin.svg" alt="Закрепить">`;
                    unpinButton.parentNode.replaceChild(revertPinButton, unpinButton);
                    // При необходимости можно повторно навесить обработчик для pin-message
                }
            }
        });
    });
    
    // Обработчик для кнопок открепления сообщений
    document.querySelectorAll('.unpin-message').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const messageId = this.getAttribute('data-message-id');
            if (!messageId) return;
            
            // Оптимистично меняем иконку сразу: unpin -> pin
            const unpinButton = this;
            const parent = unpinButton.parentNode;
            const tempPinButton = document.createElement('button');
            tempPinButton.classList.add('pin-message');
            tempPinButton.setAttribute('data-message-id', messageId);
            tempPinButton.innerHTML = `<img src="/img/icon/pin.svg" alt="Закрепить">`;
            parent.replaceChild(tempPinButton, unpinButton);
            // Можно добавить обработчик для нового pin-button, если требуется
            
            try {
                const response = await fetch(`/chats/${currentChatType}/${currentChatId}/messages/${messageId}/unpin`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'Неизвестная ошибка');
                }
            } catch (error) {
                console.error('Ошибка при откреплении сообщения:', error);
                alert('Не удалось открепить сообщение: ' + error.message);
                // В случае ошибки возвращаем прежнюю иконку: pin -> unpin
                const pinButton = document.querySelector(`.pin-message[data-message-id="${messageId}"]`);
                if (pinButton) {
                    const revertUnpinButton = document.createElement('button');
                    revertUnpinButton.classList.add('unpin-message');
                    revertUnpinButton.setAttribute('data-message-id', messageId);
                    revertUnpinButton.innerHTML = `<img src="/img/icon/unpin.svg" alt="Открепить">`;
                    pinButton.parentNode.replaceChild(revertUnpinButton, pinButton);
                    attachUnpinHandler(revertUnpinButton, csrfToken, currentChatType, currentChatId);
                }
            }
        });
    });
}

function attachUnpinHandler(button, csrfToken, currentChatType, currentChatId) {
    button.addEventListener('click', async function(e) {
        e.preventDefault();
        const messageId = this.getAttribute('data-message-id');
        if (!messageId) return;
        
        // Обновляем внешний вид кнопки сразу после клика: меняем svg и добавляем класс для стилизации
        this.innerHTML = `<img src="/img/icon/pin.svg" alt="Закрепить">`;
        this.classList.add('processing-unpin'); // Класс можно использовать для дополнительных стилей
        
        try {
            const response = await fetch(`/chats/${currentChatType}/${currentChatId}/messages/${messageId}/unpin`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Поиск сообщения по data-message-id вместо data-id
                const messageElement = document.querySelector(`.message[data-message-id="${messageId}"]`);
                if (messageElement) {
                    messageElement.classList.remove('pinned');
                    
                    // Кнопка уже заменена на pin-состояние
                    // Опционально удаляем класс дополнительного стиля
                    button.classList.remove('processing-unpin');
                }
            } else {
                throw new Error(data.error || 'Неизвестная ошибка');
            }
        } catch (error) {
            console.error('Ошибка при откреплении сообщения:', error);
            alert('Не удалось открепить сообщение: ' + error.message);
            // В случае ошибки можно вернуть исходное состояние кнопки
            this.innerHTML = `<img src="/img/icon/unpin.svg" alt="Открепить">`;
            this.classList.remove('processing-unpin');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    subscribeToNotifications();
    setInterval(fetchNewMessages, 1000);
});
