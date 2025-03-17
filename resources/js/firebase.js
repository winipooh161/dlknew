// Firebase функциональность удалена. Все функции заменены на заглушки.

export async function registerServiceWorker() {
    console.warn('Firebase: registerServiceWorker отключён');
    return null;
}

export async function requestNotificationPermission() {
    console.warn('Firebase: requestNotificationPermission отключён');
    return false;
}

export function initializeFirebaseMessaging() {
    console.warn('Firebase: initializeFirebaseMessaging отключён');
    return null;
}

export function showNotification(title, options) {
    console.warn('Firebase: showNotification отключён');
    console.log('Сообщение не показано:', { title, options });
}

export function setupForegroundNotifications() {
    console.warn('Firebase: setupForegroundNotifications отключён');
}

document.addEventListener('DOMContentLoaded', () => {
    window.requestNotificationPermission = requestNotificationPermission;
    // Инициализация Firebase выполнена не производится.
    // Импортируем функцию subscribeToNotifications для уведомлений (Firebase не используется).
    import('./notification').then(module => {
        if (typeof module.subscribeToNotifications === 'function') {
            module.subscribeToNotifications();
        }
    }).catch(error => {
        console.error('Ошибка при импорте notification.js:', error);
    });
});
  
export default {
    registerServiceWorker,
    requestNotificationPermission,
    initializeFirebaseMessaging,
    showNotification,
    setupForegroundNotifications
};
