/**
 * Модуль обработки уведомлений
 */

/**
 * Класс обработчика уведомлений для приложения
 */
class NotificationHandler {
    constructor() {
        this.hasInitialized = false;
        this.notifications = [];
        this.user = null;
        this.channels = {};
    }

    /**
     * Инициализирует обработчик уведомлений
     */
    init() {
        if (this.hasInitialized) return;

        this.hasInitialized = true;
        this.user = this.getUserInfo();

        // Ждем немного, чтобы убедиться, что Echo инициализирован
        setTimeout(() => {
            this.setupEventListeners();
        }, 2000);
    }

    /**
     * Извлекает информацию о пользователе из глобального объекта или localStorage
     * @returns {Object|null} Информация о пользователе или null
     */
    getUserInfo() {
        // Пытаемся получить информацию о пользователе из разных источников
        if (window.Laravel && window.Laravel.user) {
            return window.Laravel.user;
        }

        try {
            const userString = localStorage.getItem('user_data');
            if (userString) {
                return JSON.parse(userString);
            }
        } catch (e) {
            console.warn('Ошибка при получении данных пользователя из localStorage:', e);
        }

        return null;
    }

    /**
     * Настраивает слушателей событий для уведомлений
     */
    setupEventListeners() {
        // Безопасно проверяем наличие Echo и пользователя
        if (!window.Echo || !this.user) {
            console.warn('Echo или данные пользователя не доступны для настройки уведомлений');
            return;
        }

        try {
            // Настройка канала для личных уведомлений
            if (this.user.id) {
                this.setupPrivateChannel(`user.${this.user.id}`);
            }

            // Общий канал для всех пользователей
            this.setupPublicChannel('notifications');

        } catch (error) {
            console.error('Ошибка при настройке слушателей уведомлений:', error);
        }
    }

    /**
     * Настраивает приватный канал для уведомлений
     * @param {string} channelName Имя канала
     */
    setupPrivateChannel(channelName) {
        // Проверяем наличие Echo и возможности работы с приватными каналами
        if (!window.Echo || typeof window.Echo.private !== 'function') {
            console.warn(`Не удалось подписаться на приватный канал ${channelName}: Echo не инициализирован корректно`);
            return;
        }

        // Небольшая задержка для надежности
        setTimeout(() => {
            try {
                const channel = window.Echo.private(channelName);
                this.channels[channelName] = channel;

                // Подписка на события
                channel.listen('.notification.received', (notification) => {
                    this.handleNotification(notification);
                });

                console.log(`Подписка на приватный канал ${channelName} выполнена`);
            } catch (error) {
                console.error(`Ошибка при подписке на приватный канал ${channelName}:`, error);
            }
        }, 1000);
    }

    /**
     * Настраивает публичный канал для уведомлений
     * @param {string} channelName Имя канала
     */
    setupPublicChannel(channelName) {
        if (!window.Echo || typeof window.Echo.channel !== 'function') {
            console.warn(`Не удалось подписаться на публичный канал ${channelName}: Echo не инициализирован корректно`);
            return;
        }

        try {
            const channel = window.Echo.channel(channelName);
            this.channels[channelName] = channel;

            // Подписка на события
            channel.listen('.broadcast.message', (notification) => {
                this.handleBroadcastMessage(notification);
            });

            console.log(`Подписка на публичный канал ${channelName} выполнена`);
        } catch (error) {
            console.error(`Ошибка при подписке на публичный канал ${channelName}:`, error);
        }
    }

    /**
     * Обрабатывает полученное уведомление
     * @param {Object} notification Объект уведомления
     */
    handleNotification(notification) {
        console.log('Получено уведомление:', notification);
        
        // Сохраняем уведомление в список
        this.notifications.push(notification);
        
        // Показываем браузерное уведомление
        try {
            this.showBrowserNotification(
                notification.title || 'Новое уведомление',
                notification.message || notification.body || '',
                notification.data || {}
            );
        } catch (error) {
            console.error('Ошибка при отображении уведомления:', error);
        }
        
        // Вызываем событие для обновления UI, если необходимо
        this.triggerNotificationEvent(notification);
    }

    /**
     * Показывает браузерное уведомление
     * @param {string} title Заголовок уведомления
     * @param {string} body Текст уведомления
     * @param {Object} data Дополнительные данные
     */
    showBrowserNotification(title, body, data = {}) {
        if (!("Notification" in window)) {
            console.log('Этот браузер не поддерживает уведомления');
            return;
        }

        if (Notification.permission === 'granted') {
            const notification = new Notification(title, {
                body: body,
                icon: '/img/logo.png'
            });

            // Обработчик клика по уведомлению
            notification.onclick = function() {
                window.focus();
                if (data.url) {
                    window.open(data.url, '_blank');
                }
                notification.close();
            };
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    this.showBrowserNotification(title, body, data);
                }
            });
        }
    }

    /**
     * Обрабатывает полученное широковещательное сообщение
     * @param {Object} message Объект сообщения
     */
    handleBroadcastMessage(message) {
        console.log('Получено широковещательное сообщение:', message);
        
        // Если сообщение предназначено для конкретных пользователей, проверяем
        if (message.recipient_ids && Array.isArray(message.recipient_ids)) {
            if (!this.user || !message.recipient_ids.includes(this.user.id)) {
                console.log('Сообщение не предназначено для текущего пользователя');
                return;
            }
        }
        
        // Показываем уведомление
        this.showBrowserNotification(
            message.title || 'Уведомление',
            message.body || message.text || '',
            message.data || {}
        );
        
        // Вызываем событие для обновления UI
        this.triggerBroadcastEvent(message);
    }

    /**
     * Вызывает событие обновления UI для уведомления
     * @param {Object} notification Объект уведомления
     */
    triggerNotificationEvent(notification) {
        const event = new CustomEvent('notification:received', {
            detail: notification
        });
        document.dispatchEvent(event);
    }

    /**
     * Вызывает событие обновления UI для широковещательного сообщения
     * @param {Object} message Объект сообщения
     */
    triggerBroadcastEvent(message) {
        const event = new CustomEvent('broadcast:received', {
            detail: message
        });
        document.dispatchEvent(event);
    }
}

// Создаем экземпляр обработчика уведомлений
const notificationHandler = new NotificationHandler();

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    // Инициализация с небольшой задержкой для уверенности, что другие модули загружены
    setTimeout(() => {
        notificationHandler.init();
    }, 1500);
});

// Экспортируем объект для использования в других модулях
export default notificationHandler;

export function checkForNewMessages() {
    // Минимальная реализация для устранения ошибки импорта
    console.log('checkForNewMessages вызвана');
    // Можно добавить логику проверки новых уведомлений
}

export function showChatNotification(title, message, data) {
    // Минимальная реализация для устранения ошибки импорта
    console.log('showChatNotification вызвана', { title, message, data });
    if (notificationHandler) {
        notificationHandler.showBrowserNotification(title, message, data);
    }
}

export function subscribeToNotifications() {
    try {
        // Проверяем поддержку уведомлений в браузере
        if (!('Notification' in window)) {
            console.log('Этот браузер не поддерживает уведомления');
            return;
        }
        
        // Если разрешения уже получены
        if (Notification.permission === 'granted') {
            console.log('Разрешения на уведомления уже получены');
        } else if (Notification.permission !== 'denied') {
            // Откладываем запрос на разрешение до первого взаимодействия пользователя
            document.addEventListener('click', function handleClick() {
                Notification.requestPermission();
                document.removeEventListener('click', handleClick);
            }, { once: true });
        }
    } catch (error) {
        console.error('Ошибка при подписке на уведомления:', error);
    }
}
