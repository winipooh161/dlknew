importScripts('https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.6.1/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyB6N1n8dW95YGMMuTsZMRnJY1En7lK2s2M",
    authDomain: "dlk-diz.firebaseapp.com",
    projectId: "dlk-diz",
    storageBucket: "dlk-diz.firebasestorage.app",
    messagingSenderId: "209164982906",
    appId: "1:209164982906:web:0836fbb02e7effd80679c3"
});

const messaging = firebase.messaging();

// Улучшенная обработка фоновых сообщений
messaging.onBackgroundMessage(function(payload) {
    console.log('Получено фоновое сообщение:', payload);
    
    // Улучшение 8: Безопасная обработка payload.data и установка дефолтных значений
    let data = payload.data || {};
    data.timestamp = new Date().getTime(); // Улучшение 4: Добавляем метку времени
    
    // Улучшение 6: Закрываем предыдущие уведомления с тем же тегом
    self.registration.getNotifications({ tag: data.chatId ? `chat-${data.chatId}` : 'default' })
        .then(notifications => notifications.forEach(n => n.close()));

    const notificationTitle = payload.notification.title || 'Новое сообщение';
    const notificationOptions = {
        body: payload.notification.body || 'У вас новое уведомление',
        icon: payload.notification.icon || '/img/icons/notification-icon.png',
        badge: '/img/icons/badge-icon.png',
        image: payload.notification.image || '', // Улучшение 2: Показ изображения, если передан
        data: data,
        tag: data.chatId ? `chat-${data.chatId}` : 'default',
        renotify: true,
        vibrate: data.vibrate ? data.vibrate : [200, 100, 200], // Улучшение 10: fallback для vibrate
        requireInteraction: true, // Улучшение 1: Тримать уведомление до взаимодействия
        silent: false, // Улучшение 12: Всегда воспроизводить звук
        priority: 'max', // Улучшение 13: Максимальный приоритет
        sticky: false, // Улучшение 14: Уведомление нельзя смахнуть
        noscreen: false, // Улучшение 15: Включать экран
        timeoutAfter: 5000, // Улучшение 16: Автоматически закрывать через 5 секунд
        eventLabel: 'notification', // Улучшение 18: Для Google Analytics
        campaign: 'chat', // Улучшение 19: Для Google Analytics
        category: 'chat', // Улучшение 20: Для Google Analytics
        sound: '/sounds/notification.mp3', // Улучшение 21: Пользовательский звук
        dir: 'ltr', // Улучшение 22: Направление текста
        lang: 'ru', // Улучшение 23: Язык
        actions: [
            { action: 'open_chat', title: 'Открыть чат', icon: '/img/icons/open_chat.png' },
            { action: 'reply', title: 'Ответить', icon: '/img/icons/reply.png' }, // Улучшение 3: Добавляем кнопку "Ответить"
            { action: 'dismiss', title: 'Отменить', icon: '/img/icons/dismiss.png' }  // Улучшение 5: Кнопка для отмены
        ]
    };
    
    // Показываем уведомление
    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Улучшение 7: Обработчик закрытия уведомления для логирования
self.addEventListener('notificationclose', function(event) {
    console.log('Уведомление закрыто:', event.notification.tag);
});

// Обработчик нажатия на уведомление
self.addEventListener('notificationclick', function(event) {
    console.log('Нажатие на уведомление', event);
    
    // Закрываем уведомление
    event.notification.close();
    
    // Получаем данные из уведомления
    const chatType = event.notification.data.chatType || 'personal';
    const chatId = event.notification.data.chatId;
    
    // Формируем URL для перехода
    let url = '/chats';
    if (chatId) {
        url = `/chats?type=${chatType}&id=${chatId}`;
    }
    
    // Улучшение 9: Улучшенное логирование события клика
    console.log(`Переход по URL: ${url}`);
    
    // Открываем окно с чатом при нажатии на уведомление
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
            // Проверяем, есть ли открытые окна
            for (let i = 0; i < windowClients.length; i++) {
                const client = windowClients[i];
                // Если окно уже открыто, фокусируемся на нем
                if (client.url.includes(url) && 'focus' in client) {
                    return client.focus();
                }
            }
            // Если нет открытых окон, открываем новое
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});

// Обработчик синхронизации для отложенных действий
self.addEventListener('sync', function(event) {
    console.log('Событие синхронизации:', event.tag);
    
    if (event.tag === 'send-message') {
        // Здесь можно реализовать логику отправки сообщений,
        // когда соединение восстановлено
    }
});


