import './bootstrap';
import './chat.js'; // Инициализация функционала чата

document.addEventListener('DOMContentLoaded', () => {
	// Инициализация чата, если мы на странице чатов
    if (window.location.pathname.includes('/chats')) {
        // Чат уже инициализируется в своём скрипте
        console.log('Чат загружен');
    }
});

window.addEventListener('load', () => {
    const loadingScreen = document.getElementById('loading-screen');
    const content = document.getElementById('content');
    setTimeout(() => {
        loadingScreen.classList.add('hidden'); // Применяем класс для анимации исчезновения
        document.body.style.overflow = 'auto'; // Включаем прокрутку
        setTimeout(() => {
            loadingScreen.style.display =
                'none'; // Полностью убираем загрузку после анимации
            content.style.opacity =
                '1'; // Плавно показываем содержимое (контент уже анимируется в CSS)
        }, 1000); // Длительность анимации исчезновения (совпадает с fadeOut)
    }, 1000); // Задержка до начала исчезновения
});

// Добавляем функцию для проверки непрочитанных сообщений
window.checkUnreadMessages = function() {
    if (typeof window.Laravel !== 'undefined') {
        axios.get('/api/messages/unread-count')
            .then(response => {
                const count = response.data.count;
                const menuItem = document.querySelector('.ponel-menu .bi-chat-dots').closest('a');
                let messageBadge = menuItem.querySelector('.badge');
                
                if (count > 0) {
                    if (messageBadge) {
                        messageBadge.textContent = count;
                        messageBadge.style.display = 'inline-block';
                    } else {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-danger rounded-pill ms-1';
                        badge.textContent = count;
                        menuItem.appendChild(badge);
                    }
                } else if (messageBadge) {
                    messageBadge.style.display = 'none';
                }
            })
            .catch(error => console.error('Ошибка при проверке непрочитанных сообщений:', error));
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Проверяем сразу при загрузке
    if (typeof window.checkUnreadMessages === 'function') {
        window.checkUnreadMessages();
    }
    
    // Проверяем каждую минуту
    setInterval(function() {
        if (typeof window.checkUnreadMessages === 'function') {
            window.checkUnreadMessages();
        }
    }, 60000); // каждую минуту вместо каждой секунды
});