import { filterMessages } from './chat-utils';

// Улучшение 103: Функция централизованного логирования ошибок
function logError(error) {
    if (window.Sentry) {
        window.Sentry.captureException(error);
    } else {
        console.error(error);
    }
}

export function attachMessageActionListeners(csrfToken, currentChatType, currentChatId) {
    document.querySelectorAll('.delete-message').forEach(button => {
        button.onclick = function() {
            const messageId = this.getAttribute('data-id');
            if (confirm('Удалить сообщение?')) {
                fetch(`/chats/${currentChatType}/${currentChatId}/messages/${messageId}`, {
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
                .catch(error => logError(error));
            }
        };
    });
    document.querySelectorAll('.pin-message').forEach(button => {
        button.onclick = function() {
            const messageId = this.getAttribute('data-id');
            fetch(`/chats/${currentChatType}/${currentChatId}/messages/${messageId}/pin`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Изменено: проверяем data.message вместо data.success
                if (data.message) {
                    this.innerHTML = `<img src="${unpinImgUrl}" alt="Открепить">`;
                    this.classList.remove('pin-message');
                    this.classList.add('unpin-message');
                    let li = this.closest('li');
                    li.classList.add('pinned');
                    if (li && !li.querySelector('.pinned-label')) {
                        let span = document.createElement('span');
                        span.classList.add('pinned-label');
                        span.textContent = ' [Закреплено]';
                        li.querySelector('div').appendChild(span);
                    }
                    filterMessages();
                } else {
                    console.error('Ошибка закрепления сообщения:', data.error);
                    alert(data.error || 'Ошибка закрепления сообщения');
                }
            })
            .catch(error => {
                logError(error);
            });
        };
    });
    document.querySelectorAll('.unpin-message').forEach(button => {
        button.onclick = function() {
            const messageId = this.getAttribute('data-id');
            fetch(`/chats/${currentChatType}/${currentChatId}/messages/${messageId}/unpin`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.innerHTML = `<img src="${pinImgUrl}" alt="Закрепить">`;
                    this.classList.remove('unpin-message');
                    this.classList.add('pin-message');
                    let li = this.closest('li');
                    li.classList.remove('pinned');
                    let pinnedLabel = li.querySelector('.pinned-label');
                    if (pinnedLabel) { pinnedLabel.remove(); }
                    filterMessages();
                } else { alert(data.error || 'Ошибка открепления сообщения'); }
            })
            .catch(error => logError(error));
        };
    });
}
