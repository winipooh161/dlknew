@extends('layouts.auth')
@section('content')
<div class="container-auth">
    <div class="auth__body flex center">
        <div class="auth__form">
            <h1 class="" style="text-align: center">Вход по коду </h1>
            <p class="auth__title_sub">Мы рады видеть вас! </br>
                <strong>Войдите</strong> в свою учетную запись</p>
            <form id="auth-form" action="{{ route('login.code.post') }}" method="POST">
                @csrf
                <div id="phone-section">
                    <label for="phone">
                        
                        <input type="phone" name="phone" id="phone" class="form-control maskphone" placeholder="Введите телефон" value="{{ old('phone') }}"  maxlength="50" required>
                        <div id="phone-error" class="error-message"></div>
                    </label>
                    <button type="button" id="send-code-btn" class="btn btn-primary">Получить код</button>
                    <ul class="auth__form__link">
                       
                        <li class="politic__info" style="text-align: center">Нажимая кнопку «Получить код», вы принимаете 
                 <a href=""> условия пользовательского соглашения</a></li>
                    </ul>
                </div>
                <div id="code-section" class="hidden">
                    <div class="code-inputs">
                        @for ($i = 0; $i < 4; $i++)
                            <input type="text" class="code-input" placeholder=""  maxlength="1" required>
                        @endfor
                    </div>
                    <input type="hidden" name="code" id="code" value="">
                    <div id="code-error" class="error-message"></div> 
                    <div class="code-section-link">
                        <a href="#" id="resend-code-link" class="disabled-link">Отправить код повторно</a>
                        <p id="resend-timer" style="display: none;">Получить код повторно можно через <span id="resend-countdown">60</span> секунд.</p>
                    </div>
                </div>
            </form>
            <ul class="auth__form__link">
                <li class="else__auth">---------- или ----------</li>
                <li><a href="{{ route('login.password') }}">Войти с паролем</a></li>
                <li><a href="{{ route('register') }}">Регистрация</a></li>
            </ul>
        </div>
    </div>
</div>
<style>
    .hidden { display: none; }
    .code-inputs {
        display: flex;
        gap: 5px;
    }
  
    .error-message {
        color: red;
        font-size: 0.875rem;
        margin-top: 5px;
    }
</style>
<script>
   document.addEventListener('DOMContentLoaded', function () {
    const sendCodeBtn = document.getElementById('send-code-btn');
    const phoneSection = document.getElementById('phone-section');
    const codeSection = document.getElementById('code-section');
    const resendLink = document.getElementById('resend-code-link');
    const resendTimer = document.getElementById('resend-timer');
    const countdownSpan = document.getElementById('resend-countdown');
    const form = document.getElementById('auth-form');
    const codeInputs = document.querySelectorAll('.code-input');
    const codeField = document.getElementById('code');
    const phoneError = document.getElementById('phone-error');
    const codeError = document.getElementById('code-error');
    let countdownInterval = null;
    function sendCode(phone) {
        if (!phone) {
            phoneError.textContent = 'Введите номер телефона!';
            return Promise.reject('Номер телефона отсутствует.');
        }
        phoneError.textContent = '';  
        return fetch("{{ route('send.code') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ phone })
        }).then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(text || 'Ошибка на сервере');
                });
            }
            return response.json();
        });
    }
    sendCodeBtn.addEventListener('click', function () {
        const phone = document.getElementById('phone').value;
        sendCode(phone)
            .then(data => {
                if (data.success) {
                    phoneSection.classList.add('hidden');
                    codeSection.classList.remove('hidden');
                    startResendCooldown();
                } else {
                    phoneError.textContent = data.error || 'Ошибка отправки кода.';
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                phoneError.textContent = 'Произошла ошибка при отправке запроса. Пожалуйста, попробуйте снова.';
            });
    });
    resendLink.addEventListener('click', function (e) {
        e.preventDefault();
        if (resendLink.classList.contains('disabled-link')) {
            return; 
        }
        const phone = document.getElementById('phone').value;
        sendCode(phone)
            .then(data => {
                if (data.success) {
                    alert('Код был отправлен повторно.');
                    startResendCooldown();
                } else {
                    codeError.textContent = data.error || 'Ошибка отправки кода.';
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                codeError.textContent = 'Произошла ошибка при отправке запроса. Пожалуйста, попробуйте снова.';
            });
    });
    function startResendCooldown() {
        let remainingTime = 60; // 60 seconds
        resendLink.classList.add('disabled-link');
        resendTimer.style.display = 'block';
        countdownSpan.textContent = remainingTime;
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }
        countdownInterval = setInterval(() => {
            remainingTime--;
            countdownSpan.textContent = remainingTime;
            if (remainingTime <= 0) {
                clearInterval(countdownInterval);
                resendLink.classList.remove('disabled-link');
                resendTimer.style.display = 'none';
            }
        }, 1000);
    }
    codeInputs.forEach((input, index) => {
        input.addEventListener('input', function () {
            if (input.value.length > 0 && index < codeInputs.length - 1) {
                codeInputs[index + 1].focus();
            }
            const code = Array.from(codeInputs).map(input => input.value).join('');
            if (code.length === 4) {
                codeField.value = code;
                form.submit();
            }
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !input.value && index > 0) {
                codeInputs[index - 1].focus();
            }
        });
    });
});
</script>
