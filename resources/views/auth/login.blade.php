@extends('layouts.auth')
@section('content')
    <div class="container-auth">
        <div class="auth__body flex center">
            <div class="auth__form">
                <h1>Войти</h1>
                <p class="auth__title_sub">Мы рады видеть вас! </br>
                    <strong>Войдите</strong> в свою учетную запись</p>
                <form action="{{ route('login.code.post') }}" method="POST" id="login-form">
                    @csrf
                    <label for="phone" id="phone-label">
                        
                        <input type="text" name="phone" id="phone" class="form-control maskphone" placeholder="Введите телефон" value="{{ old('phone') }}" required>
                        @error('phone')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </label>
                    <!-- Код для входа (спрятан по умолчанию) -->   
                    <div id="code-fields" style="display: none;">
                        <label for="code">
                            <p>Введите код:</p>
                            <div class="code-inputs">
                                <input type="text" id="code1" maxlength="1"placeholder="X" class="form-control code-input" oninput="moveFocus(this, 'code2')" required>
                                <input type="text" id="code2" maxlength="1"placeholder="X" class="form-control code-input" oninput="moveFocus(this, 'code3')" required>
                                <input type="text" id="code3" maxlength="1"placeholder="X" class="form-control code-input" oninput="moveFocus(this, 'code4')" required>
                                <input type="text" id="code4" maxlength="1"placeholder="X" class="form-control code-input" required>
                            </div>
                            @error('code')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>
                    <!-- Ссылка для отправки кода -->
                    <a href="#" id="send-code-btn" class="btn btn-secondary">Отправить код</a>
                    <p id="timer" style="display:none; color: red; margin-top: 10px;">Повторная отправка доступна через <span id="time-remaining">60</span> секунд</p>
                    <!-- Пароль (по умолчанию скрыт) -->
                    <div id="password-fields" style="display: none;">
                        <label for="password">
                            
                            <input type="password" name="password" id="password" placeholder="Пароль"class="form-control" required>
                            @error('password')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary" id="login-btn" style="display: none;">Войти</button>
                    <ul class="auth__form__link">
                        <li class="else__auth">---------- или ----------</li>
                        <li><a href="{{ url('/registration') }}">Регистрация</a></li>
                        <li><a href="#" id="toggle-login-method">Войти с паролем</a></li>
                    </ul>
                </form>
            </div>
        </div>
    </div>
    <script>
        // Функция для переключения фокуса между полями ввода
        function moveFocus(currentInput, nextInputId) {
            if (currentInput.value.length === 1) {
                const nextInput = document.getElementById(nextInputId);
                if (nextInput) {
                    nextInput.focus();
                }
            }
        }
        // Таймер для отсчета времени до повторной отправки кода
        let timeRemaining = 60;
        let timerInterval;
        // Функция для запуска таймера
        function startTimer() {
            document.getElementById('timer').style.display = 'block';
            document.getElementById('send-code-btn').style.pointerEvents = 'none'; // Отключаем кнопку отправки
            timerInterval = setInterval(function() {
                timeRemaining--;
                document.getElementById('time-remaining').textContent = timeRemaining;
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    document.getElementById('send-code-btn').style.pointerEvents = 'auto'; // Включаем кнопку обратно
                    document.getElementById('send-code-btn').textContent = 'Отправить код';
                    timeRemaining = 60;
                    document.getElementById('timer').style.display = 'none';
                }
            }, 1000);
        }
        // Показываем блок с кодом и скрываем поле телефона при нажатии на кнопку "Отправить код"
        document.getElementById('send-code-btn').addEventListener('click', function(event) {
            event.preventDefault();
            const phoneLabel = document.getElementById('phone-label');
            const codeFields = document.getElementById('code-fields');
            const sendCodeBtn = document.getElementById('send-code-btn');
            // Скрываем поле телефона
            phoneLabel.style.display = 'none';
            // Показываем блок для ввода кода
            codeFields.style.display = 'block';
            // Запуск таймера и отправка кода
            startTimer();
            // Здесь можно отправить AJAX запрос для получения кода (опционально)
            // Например, через fetch() или axios для асинхронного запроса
        });
        // Переключение метода входа (по коду или по паролю)
        document.getElementById('toggle-login-method').addEventListener('click', function(event) {
            event.preventDefault();
            const passwordFields = document.getElementById('password-fields');
            const codeFields = document.getElementById('code-fields');
            const toggleButton = document.getElementById('toggle-login-method');
            const loginBtn = document.getElementById('login-btn');
            // Переключаем метод входа
            if (codeFields.style.display === 'block') {
                // Показываем вход по паролю
                passwordFields.style.display = 'block';
                codeFields.style.display = 'none';
                toggleButton.textContent = 'Зайти по коду'; // Меняем текст ссылки
                loginBtn.style.display = 'inline'; // Показываем кнопку "Войти"
            } else {
                // Показываем вход по коду
                passwordFields.style.display = 'none';
                codeFields.style.display = 'block';
                toggleButton.textContent = 'Войти с паролем'; // Меняем текст ссылки
                loginBtn.style.display = 'none'; // Скрываем кнопку "Войти"
            }
        });
        // Автоматическая отправка формы, если все поля заполнены
        document.querySelectorAll('.code-input').forEach(input => {
            input.addEventListener('input', function() {
                // Проверка, что все поля для кода заполнены
                const allFilled = Array.from(document.querySelectorAll('.code-input')).every(input => input.value.length === 1);
                if (allFilled) {
                    // Отправка формы
                    document.getElementById('login-form').submit();
                }
            });
        });
    </script>
@endsection
