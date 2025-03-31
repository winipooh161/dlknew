/**
 * Основной файл инициализации для различных сервисов приложения
 */

import axios from 'axios';
import 'bootstrap'; // Инициализация Bootstrap компонентов

// Настройка Axios
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
}

// Добавляем глобальный обработчик ошибок для Axios
axios.interceptors.response.use(
    response => response,
    error => {
        // Логируем ошибки
        if (error.response) {
            console.error('Ошибка запроса:', error.response.status, error.response.data);
        } else if (error.request) {
            console.error('Ошибка соединения:', error.request);
        } else {
            console.error('Ошибка:', error.message);
        }
        
        return Promise.reject(error);
    }
);

const maskElements = document.querySelectorAll('.mask-input');
if (!maskElements.length) {
    console.debug('Элементы для масок не найдены');
} else {
    // Работа с элементами maskElements...
}

// Инициализируем все при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    // Диагностика и исправление ошибок
    handleDomErrors();
});

/**
 * Обновляет CSRF-токен для предотвращения его устаревания
 */
function refreshCsrfToken() {
    fetch('/refresh-csrf', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.token) {
            // Обновляем токен в метатеге
            const tokenElement = document.querySelector('meta[name="csrf-token"]');
            if (tokenElement) {
                tokenElement.setAttribute('content', data.token);
                // Обновляем заголовок в axios
                window.axios.defaults.headers.common['X-CSRF-TOKEN'] = data.token;
                console.log('CSRF-токен успешно обновлен');
            }
        }
    })
    .catch(error => {
        console.error('Ошибка при обновлении CSRF-токена:', error);
    });
}

// Экспортируем функции для использования в других модулях
window.refreshCsrfToken = refreshCsrfToken;

// Автоматическое обновление CSRF-токена каждые 30 минут
setInterval(refreshCsrfToken, 30 * 60 * 1000);