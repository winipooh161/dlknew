/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Устанавливаем CSRF-токен для всех AJAX запросов
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF токен не найден: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Инициализация Firebase с оптимизированной конфигурацией и дополнительной обработкой ошибок
import { initializeApp } from 'firebase/app';
import { getMessaging, onMessage } from 'firebase/messaging';
import { getFirestore } from 'firebase/firestore';

const firebaseConfig = {
    apiKey: import.meta.env.VITE_FIREBASE_API_KEY || 'AIzaSyB6N1n8dW95YGMMuTsZMRnJY1En7lK2s2M',
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN || 'dlk-diz.firebaseapp.com',
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID || 'dlk-diz.firebasestorage.app',
    storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET || 'dlk-diz.firebasestorage.app',
    messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID || '209164982906',
    appId: import.meta.env.VITE_FIREBASE_APP_ID || '1:209164982906:web:0836fbb02e7effd80679c3',
};

const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);
window.firestore = getFirestore(app);

onMessage(messaging, (payload) => {
  console.log('Получено сообщение: ', payload);
  // Обработка уведомлений (улучшение 35, 86)
});

document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Улучшение 106: Глобальный обработчик ошибок для fetch-запросов
window.addEventListener('error', function(event) {
    if (window.Sentry) {
        window.Sentry.captureException(event.error);
    } else {
        console.error(event.error);
    }
});

// Оборачиваем fetch-запросы в блок обработки ошибок и добавляем проверку статуса ответа.
if (typeof url !== 'undefined' && typeof data !== 'undefined') {
    fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // ...обработка данных...
    })
    .catch(error => {
        console.error('Ошибка в POST запросе:', error);
    });
} else {
    console.error('Переменные url или data не определены для fetch.');
}