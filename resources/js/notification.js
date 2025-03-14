import app from './firebase-init';
import { getMessaging, onMessage, getToken } from 'firebase/messaging';

const messaging = getMessaging(app);

const currentUserId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');

// Новая функция запроса всех разрешений
function requestAllPermissions() {
    // Запрос разрешения на уведомления, если не решено
    if (Notification.permission !== 'granted' && Notification.permission !== 'denied') {
        Notification.requestPermission().then(permission => {
            console.log('Notification permission:', permission);
        });
    }
    // Запрос разрешения на геолокацию (если требуется для работы сайта)
    if ('geolocation' in navigator) {
        navigator.geolocation.getCurrentPosition(
            position => console.log('Geolocation permission granted:', position),
            error => console.error('Geolocation error:', error)
        );
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Запрашиваем все необходимые разрешения
    requestAllPermissions();
    
    if (Notification.permission === 'granted') { 
        obtainAndSendToken(); 
    }
    
    if (!window.firebaseNotifHandlerRegistered) {
        onMessage(messaging, payload => {
            console.log('Получено push‑уведомление:', payload);
            if(payload.data && payload.data.recipient_id && payload.data.recipient_id !== currentUserId) {
                return;
            }
            // Новая логика формирования сообщения уведомления
            const sender = payload.data?.sender_name || 'Неизвестно';
            const message = payload.data?.message_text || payload.notification?.body || '';
            const title = `Новое сообщение от: ${sender}`;
            const body = `Сообщение: ${message}`;
            const options = {
                body: body,
                icon: payload.notification?.icon || '/firebase-logo.png',
                tag: `global-notif-${Date.now()}`
            };
            let notif = new Notification(title, options);
            notif.onclick = function(event) {
                event.preventDefault();
                window.focus();
                if (payload.data && payload.data.chat_id && payload.data.chat_type) {
                    window.location.href = `/chats/${payload.data.chat_type}/${payload.data.chat_id}`;
                }
            };
            // Изменяем путь на корректный: 
            new Audio('/storage/avatars/firebase-sound.mp3').play();
        });
        window.firebaseNotifHandlerRegistered = true;
    }
    pollUnreadCounts();
    
    if (window.Echo) {
        window.Echo.channel('global-notifications')
            .listen('.GlobalNotification', (event) => {
                console.log('Global notification received:', event);
                const title = event.title || 'Уведомление';
                const body = event.message || '';
                showChatNotification(title, body, event.data);
            });
    }
});

function obtainAndSendToken() {
    getToken(messaging, { 
        vapidKey: 'BLf08mEO3lePyBvZCwTzaSNX9R981qwESUblCemdDVZUT_cs4G3GD2YY38CN8ELIcPmgVRZ92G7ePzY187d4Dh4'
    })
    .then((token) => {
        if (token) {
            console.log('Получен FCM токен:', token.substring(0, 20) + '...');
            fetch('/firebase/update-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ token })
            })
            .then(response => {
                if (response.ok) {
                    console.log('Токен успешно сохранён на сервере');
                } else {
                    console.error('Ошибка при сохранении токена на сервере');
                }
            })
            .catch(err => console.error('Ошибка отправки токена:', err));
        } else {
            console.error('Не удалось получить токен FCM');
        }
    })
    .catch(err => console.error('Ошибка получения токена FCM:', err));
}

export function showChatNotification(title, body, data = {}) {
    if(data.sender_name && data.message_text) {
        title = `Новое сообщение от: ${data.sender_name}`;
        body = `Сообщение: ${data.message_text}`;
    }
    let notif = new Notification(title, {
        body: body,
        icon: '/firebase-logo.png',
        data: data
    });
    notif.onclick = function(e) {
        e.preventDefault();
        window.focus();
    };
    // Воспроизводим звуковое уведомление
    new Audio('/storage/avatars/firebase-sound.mp3').play();
}

export function checkForNewMessages() {
    if (!window.firebaseNotifHandlerRegistered) {
        onMessage(messaging, (payload) => {
            if(payload.data && payload.data.recipient_id && payload.data.recipient_id !== currentUserId) { return; }
            const title = payload.data?.sender_name 
                ? `Новое сообщение от ${payload.data.sender_name}` 
                : (payload.notification?.title || 'Новое уведомление');
            const body = payload.data?.message_text 
                ? `Сообщение: ${payload.data.message_text}` 
                : (payload.notification?.body || '');
            showChatNotification(title, body, payload.data);
        });
        window.firebaseNotifHandlerRegistered = true;
    }
}

function pollUnreadCounts() {
    let lastTotal = 0;
    setInterval(() => {
        fetch('/chats/unread-counts', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                if(data.unread_counts && data.unread_counts.length > 0) {
                    const totalUnread = data.unread_counts.reduce((sum, chat) => sum + parseInt(chat.unread_count || 0), 0);
                    if(totalUnread > lastTotal) {
                        const title = 'Новые сообщения';
                        const body = `У вас появилось ${totalUnread - lastTotal} новых сообщений.`;
                        new Notification(title, {
                            body,
                            icon: '/firebase-logo.png'
                        });
                    }
                    lastTotal = totalUnread;
                } else {
                    lastTotal = 0;
                }
            } catch (e) {
                console.error('Ошибка при получении непрочитанных сообщений: Неверный формат JSON', text);
            }
        })
        .catch(err => console.error('Ошибка при получении непрочитанных сообщений:', err));
    }, 10000);
}
