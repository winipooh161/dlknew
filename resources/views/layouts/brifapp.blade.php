<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title_site }}</title>
    <!-- Scripts -->
     <!-- Обязательный (и достаточный) тег для браузеров -->
     <link type="image/x-icon" rel="shortcut icon" href="{{ asset('/favicon.ico') }}">

     <!-- Дополнительные иконки для десктопных браузеров -->
     <link type="image/png" sizes="16x16" rel="icon" href="{{ asset('/icons/favicon-16x16.png') }}">
     <link type="image/png" sizes="32x32" rel="icon" href="{{ asset('/icons/favicon-32x32.png') }}">
     <link type="image/png" sizes="96x96" rel="icon" href="{{ asset('/icons/favicon-96x96.png') }}">
     <link type="image/png" sizes="120x120" rel="icon" href="{{ asset('/icons/favicon-120x120.png') }}">
 
     <!-- Иконки для Android -->
     <link type="image/png" sizes="72x72" rel="icon" href="{{ asset('/icons/android-icon-72x72.png') }}">
     <link type="image/png" sizes="96x96" rel="icon" href="{{ asset('/icons/android-icon-96x96.png') }}">
     <link type="image/png" sizes="144x144" rel="icon" href="{{ asset('/icons/android-icon-144x144.png') }}">
     <link type="image/png" sizes="192x192" rel="icon" href="{{ asset('/icons/android-icon-192x192.png') }}">
     <link type="image/png" sizes="512x512" rel="icon" href="{{ asset('/icons/android-icon-512x512.png') }}">
     <link rel="manifest" href="./manifest.json">
 
     <!-- Иконки для iOS (Apple) -->
     <link sizes="57x57" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-57x57.png') }} ">
     <link sizes="60x60" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-60x60.png') }} ">
     <link sizes="72x72" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-72x72.png') }} ">
     <link sizes="76x76" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-76x76.png') }} ">
     <link sizes="114x114" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-114x114.png') }} ">
     <link sizes="120x120" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-120x120.png') }} ">
     <link sizes="144x144" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-144x144.png') }} ">
     <link sizes="152x152" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-152x152.png') }} ">
     <link sizes="180x180" rel="apple-touch-icon" href="{{ asset('/icons/apple-touch-icon-180x180.png') }} ">
 
     <!-- Иконки для MacOS (Apple) -->
     <link color="#e52037" rel="mask-icon" href="./safari-pinned-tab.svg">
 
     <!-- Иконки и цвета для плиток Windows -->
     <meta name="msapplication-TileColor" content="#2b5797">
     <meta name="msapplication-TileImage" content="./mstile-144x144.png">
     <meta name="msapplication-square70x70logo" content="./mstile-70x70.png">
     <meta name="msapplication-square150x150logo" content="./mstile-150x150.png">
     <meta name="msapplication-wide310x150logo" content="./mstile-310x310.png">
     <meta name="msapplication-square310x310logo" content="./mstile-310x150.png">
     <meta name="application-name" content="My Application">
     <meta name="msapplication-config" content="./browserconfig.xml">

     
    @vite(['resources/css/style.css', 'resources/css/font.css', 'resources/js/notification.js', 'resources/css/element.css', 'resources/js/bootstrap.js', 'resources/css/animation.css', 'resources/css/mobile.css', 'resources/js/modal.js', 'resources/js/success.js', 'resources/js/mask.js', 'resources/js/login.js', 'resources/js/chat.js',  

    'resources/js/message-actions.js',
    'resources/js/emoji-picker.js',
    'resources/js/chat-utils.js','resources/js/chat-main.js'])
<body>
   
    @if(session('success'))
    <div id="success-message" class="success-message">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div id="error-message" class="error-message">
        {{ session('error') }}
    </div>
    @endif
    <div id="loading-screen">
        <img src="/storage/icon/fool_logo.svg" alt="Loading">
    </div>
  
        <main class="py-4">
            @yield('content')  @include('layouts/mobponel')
        </main>
    </div>
    <div class="question_class-button">
       <a href="#top-title" class="top-title"><img src="/storage/icon/send.svg" alt=""></a>
    </div>
    <script>
        const unreadCounter = document.getElementById('unread-count');

        async function fetchUnreadMessagesCount() {
            try {
                const response = await fetch('/messages/unread-total');
                if (response.ok) {
                    const data = await response.json();
                    unreadCounter.textContent = data.total_unread_count || 0;
                } else {
                    console.error('Ошибка получения данных о непрочитанных сообщениях');
                }
            } catch (error) {
                console.error('Ошибка запроса:', error);
            }
        }

        // Обновляем данные каждые 10 секунд
        setInterval(fetchUnreadMessagesCount, 2000);

        // Первый вызов для немедленного обновления
        fetchUnreadMessagesCount();
    </script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');
    // Анимация появления сообщения об успехе
    if (successMessage) {
        successMessage.classList.add('show');
        setTimeout(() => {
            successMessage.classList.remove('show');
        }, 5000);
        successMessage.addEventListener('click', () => {
            successMessage.classList.remove('show');
        });
    }
    // Анимация появления сообщения об ошибке
    if (errorMessage) {
        errorMessage.classList.add('show');
        setTimeout(() => {
            errorMessage.classList.remove('show');
        }, 5000);
        errorMessage.addEventListener('click', () => {
            errorMessage.classList.remove('show');
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {
    var inputs = document.querySelectorAll("input.maskphone");
    for (var i = 0; i < inputs.length; i++) {
        var input = inputs[i];
        input.addEventListener("input", mask);
        input.addEventListener("focus", mask);
        input.addEventListener("blur", mask);
    }
    function mask(event) {
        var blank = "+_ (___) ___-__-__";
        var i = 0;
        var val = this.value.replace(/\D/g, "").replace(/^8/, "7").replace(/^9/, "79");
        this.value = blank.replace(/./g, function (char) {
            if (/[_\d]/.test(char) && i < val.length) return val.charAt(i++);
            return i >= val.length ? "" : char;
        });
        if (event.type == "blur") {
            if (this.value.length == 2) this.value = "";
        } else {
            setCursorPosition(this, this.value.length);
        }
    }
    function setCursorPosition(elem, pos) {
        elem.focus();
        if (elem.setSelectionRange) {
            elem.setSelectionRange(pos, pos);
            return;
        }
        if (elem.createTextRange) {
            var range = elem.createTextRange();
            range.collapse(true);
            range.moveEnd("character", pos);
            range.moveStart("character", pos);
            range.select();
            return;
        }
    }
});
document.addEventListener('DOMContentLoaded', function () {
    const nameInputs = document.querySelectorAll('input[name="name"]');
    nameInputs.forEach(input => {
        input.addEventListener('input', function () {
            // Удаляем все символы, которые не являются русскими буквами
            this.value = this.value.replace(/[^А-Яа-яЁё\s\-]/g, '');
        });
    });
});
window.addEventListener('load', () => {
    const loadingScreen = document.getElementById('loading-screen');
    const content = document.getElementById('content');
    setTimeout(() => {
        loadingScreen.classList.add('hidden'); // Применяем класс для анимации исчезновения
        document.body.style.overflow = 'auto'; // Включаем прокрутку
        setTimeout(() => {
            loadingScreen.style.display = 'none'; // Полностью убираем загрузку после анимации
            content.style.opacity = '1'; // Плавно показываем содержимое (контент уже анимируется в CSS)
        }, 1000); // Длительность анимации исчезновения (совпадает с fadeOut)
    }, 1000); // Задержка до начала исчезновения
});
document.addEventListener("DOMContentLoaded", () => {
    // Находим все textarea на странице
    const textareas = document.querySelectorAll("textarea");
    // Применяем обработчик ко всем textarea
    textareas.forEach((textarea) => {
        textarea.addEventListener("input", (event) => {
            // Разрешенные символы: английские, русские буквы, цифры, пробелы, запятые, точки, тире и символ рубля (₽)
            const allowedChars = /^[a-zA-Zа-яА-ЯёЁ0-9\s,.\-₽]*$/;
            const value = event.target.value;
            // Если введены запрещенные символы, удаляем их
            if (!allowedChars.test(value)) {
                event.target.value = value.replace(/[^a-zA-Zа-яА-ЯёЁ0-9\s,.\-₽]/g, "");
            }
        });
    });
});

</script>

<script>
    // Функция для сохранения состояния полей и чекбоксов
function saveFormState() {
    const currentURL = window.location.href; // Получаем текущий URL страницы
    const formState = {};

    // Проходим по всем полям формы и чекбоксам на странице
    document.querySelectorAll('input, select, textarea').forEach(el => {
        // Пропускаем элементы с пустыми значениями или с неактивными полями
        if (!el.name || el.disabled || el.type === 'submit') return;

        // Сохраняем значения полей в объект
        if (el.type === 'checkbox' || el.type === 'radio') {
            formState[el.name] = el.checked; // Для чекбоксов и радио кнопок сохраняем состояние
        } else {
            formState[el.name] = el.value; // Для других элементов сохраняем значение
        }
    });

    // Сохраняем состояние в localStorage в зависимости от URL
    localStorage.setItem(currentURL, JSON.stringify(formState));
}

// Функция для загрузки состояния формы
function loadFormState() {
    const currentURL = window.location.href; // Получаем текущий URL страницы
    const formState = localStorage.getItem(currentURL); // Получаем сохранённые данные для текущего URL

    if (formState) {
        const state = JSON.parse(formState);

        // Проходим по всем полям и восстанавливаем их состояние
        document.querySelectorAll('input, select, textarea').forEach(el => {
            if (!el.name || el.disabled || el.type === 'submit') return;

            if (el.type === 'checkbox' || el.type === 'radio') {
                el.checked = state[el.name] || false; // Восстанавливаем для чекбоксов и радио кнопок
            } else {
                el.value = state[el.name] || ''; // Восстанавливаем для других элементов
            }
        });
    }
}

// Слушаем события для сохранения состояния при изменении полей
document.addEventListener('input', saveFormState);
document.addEventListener('change', saveFormState);

// Загружаем сохранённое состояние при загрузке страницы
window.addEventListener('load', loadFormState);
    
</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
    const toggleButton = document.getElementById('toggle-panel');
    const panel = document.querySelector('.main__ponel');
    // Проверяем сохраненное состояние панели в localStorage
    const isCollapsed = localStorage.getItem('panelCollapsed') === 'true';
    if (isCollapsed) {
        panel.classList.add('collapsed');
    }
    // Обработчик клика по кнопке переключения
    toggleButton.addEventListener('click', () => {
        panel.classList.toggle('collapsed');
        // Сохраняем текущее состояние панели в localStorage
        const collapsed = panel.classList.contains('collapsed');
        localStorage.setItem('panelCollapsed', collapsed);
    });
});

</script>
<script>
    function refreshCsrfToken() {
        fetch('{{ route('refresh-csrf') }}')
            .then(response => response.json())
            .then(data => {
                document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
                document.querySelectorAll('input[name="_token"]').forEach(input => input.value = data.token);
            });
    }

    setInterval(refreshCsrfToken, 60000); // Обновление каждые 10 минут
</script>
</body>
</html>
