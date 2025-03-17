/**
 * Основной файл инициализации для различных сервисов приложения
 */

import axios from 'axios';

// Настройка Axios
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
}

// Добавляем кнопку для включения уведомлений, если пользователь авторизован
function addNotificationButton() {
    const userAuthenticated = document.querySelector('[data-user-authenticated="true"]');
    if (userAuthenticated) {
        // Ищем подходящий контейнер для размещения кнопки
        const container = document.querySelector('.user-profile-menu') || 
                          document.querySelector('.user-menu') || 
                          document.querySelector('.header-actions');
        
        if (container) {
            const button = document.createElement('button');
            button.setAttribute('data-notification-permission', 'true');
            button.className = 'btn btn-sm btn-outline-primary';
            button.textContent = 'Включить уведомления';
            container.appendChild(button);
        }
    }
}



// Обработка ошибок DOM
function handleDomErrors() {
    // Общий обработчик для исправления "Cannot read properties of null"
    setTimeout(() => {
        try {
            const someElement = document.querySelector('.some-element');
            if (!someElement) {
                console.log('Элемент с классом ".some-element" не найден');
            } else {
                someElement.style.display = 'block';
            }
        } catch (error) {
            console.warn(error);
        }
    }, 500);
    
    // Обработка ошибок в mask.js
    setTimeout(() => {
        try {
            const maskElements = document.querySelectorAll('.mask-element');
            if (maskElements.length === 0) {
                console.log('Элементы для масок не найдены');
            } else {
                maskElements.forEach(element => {
                    if (element && element.style) {
                        // Безопасное применение стилей
                    }
                });
            }
        } catch (error) {
            console.warn(error);
        }
    }, 1000);
}

// Улучшенная проверка элементов перед применением масок
const someElement = document.querySelector('.some-element');
if (!someElement) {
    console.debug('Элемент с классом ".some-element" не найден');
} else {
    // Работа с элементом someElement...
}

const maskElements = document.querySelectorAll('.mask-input');
if (!maskElements.length) {
    console.debug('Элементы для масок не найдены');
} else {
    // Работа с элементами maskElements...
}

// Инициализируем все при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    // Добавление кнопки для уведомлений
    addNotificationButton();
    
    // Диагностика и исправление ошибок
    checkEchoStatus();
    handleDomErrors();

    // Импортируем функцию subscribeToNotifications
    import('./notification').then(module => {
        if (typeof module.subscribeToNotifications === 'function') {
            module.subscribeToNotifications();
        }
    }).catch(error => {
        console.error('Ошибка при импорте notification.js:', error);
    });
    
    // Запускаем проверку новых сообщений с интервалом 1 секунда
    let chatUpdateInterval;
    
    // Очищаем предыдущий интервал при его наличии
    if (chatUpdateInterval) {
        clearInterval(chatUpdateInterval);
    }
    
    // Устанавливаем новый интервал
    chatUpdateInterval = setInterval(() => {
        if (typeof window.fetchNewMessages === 'function') {
            console.log('Проверка новых сообщений...');
            window.fetchNewMessages();
        } else {
            console.warn('Функция fetchNewMessages не найдена в глобальном контексте');
            
            // Попытаемся импортировать функцию fetchNewMessages, если она не доступна
            if (!window.fetchNewMessages) {
                import('./chat-utils.js').then(module => {
                    if (module.fetchNewMessages) {
                        window.fetchNewMessages = module.fetchNewMessages;
                        window.fetchNewMessages();
                    }
                }).catch(err => {
                    console.error('Не удалось импортировать fetchNewMessages:', err);
                });
            }
        }
    }, 1000);
});

// Функция проверки подключения Echo (заглушка)
function isEchoConnected() {
    return false;
}