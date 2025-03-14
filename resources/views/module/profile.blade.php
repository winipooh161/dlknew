
<h1 class="flex">
    Ваш профиль
</h1>

<div class="profile flex between">
    <!-- Левая колонка: Аватар и базовая информация -->
    <div class="profile__info center bgfff flex wow fadeInLeft" data-wow-duration="1.5s" data-wow-delay="1.5s">
        <div class="status__profile">
       
                <a href="">{{ $user->status ?? 'Не установлен' }}</a>
           
        </div>
        <div class="profile__avatar">
            <form id="update-avatar-form" action="{{ route('profile.update_avatar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <label for="avatar-input">
                    <img src="{{ $user->avatar_url ? asset($user->avatar_url) : asset('storage/avatars/group_default.svg') }}" alt="Фото пользователя">
                </label>
                <input id="avatar-input" type="file" name="avatar" accept="image/*" style="display: none;" onchange="document.getElementById('update-avatar-form').submit();">
                @if ($errors->has('avatar'))
                    <div class="error-message">
                        @foreach ($errors->get('avatar') as $message)
                            <p>{{ $message }}</p>
                        @endforeach 
                    </div>
                @endif
            </form>
            <div class="hover__create">
                <!-- SVG-иконка карандаша -->
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <mask id="mask0_1358_2896" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="3" y="3" width="18" height="18"> <path d="M3 11C3 7.22876 3 5.34315 4.17157 4.17157C5.34315 3 7.22876 3 11 3H13C16.7712 3 18.6569 3 19.8284 4.17157C21 5.34315 21 7.22876 21 11V13C21 16.7712 21 18.6569 19.8284 19.8284C18.6569 21 16.7712 21 13 21H11C7.22876 21 5.34315 21 4.17157 19.8284C3 18.6569 3 16.7712 3 13V11Z" fill="#273B4A"></path> </mask> <g mask="url(#mask0_1358_2896)"> <path d="M5.40989 12.5901L5.25713 12.7429C4.27646 13.7235 3.78613 14.2139 3.49264 14.8158C3.39066 15.025 3.30712 15.2427 3.24299 15.4664C3.05843 16.1102 3.09488 16.8027 3.16777 18.1877L3.5 24.5H21V19.7573C21 18.3059 21 17.5802 20.7614 16.9207C20.6962 16.7404 20.6181 16.565 20.5277 16.3959C20.1971 15.7774 19.6577 15.2919 18.5789 14.321L18.3643 14.1279C17.4682 13.3214 17.0202 12.9182 16.5078 12.8039C16.1864 12.7322 15.8523 12.741 15.5352 12.8295C15.0295 12.9705 14.6033 13.3967 13.7508 14.2492C13.1184 14.8816 12.8023 15.1977 12.4625 15.2406C12.2519 15.2672 12.0383 15.226 11.8526 15.1231C11.5531 14.9572 11.3742 14.5399 11.0166 13.7053C10.2559 11.9304 9.87554 11.0429 9.22167 10.7151C8.89249 10.5501 8.52413 10.4792 8.1572 10.5101C7.42836 10.5716 6.75554 11.2445 5.40989 12.5901L5.40989 12.5901Z" fill="#2A4157" fill-opacity="0.24" stroke="#222222"></path> </g> <path d="M3 11C3 7.22876 3 5.34315 4.17157 4.17157C5.34315 3 7.22876 3 11 3H13C16.7712 3 18.6569 3 19.8284 4.17157C21 5.34315 21 7.22876 21 11V13C21 16.7712 21 18.6569 19.8284 19.8284C18.6569 21 16.7712 21 13 21H11C7.22876 21 5.34315 21 4.17157 19.8284C3 18.6569 3 16.7712 3 13V11Z" stroke="#222222" stroke-width="1.2"></path> <circle cx="16.5" cy="7.5" r="1.5" fill="#222222"></circle> </g></svg>
            </div>
        </div>
        <div class="button__a__profile">
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Выйти из аккаунта</a>
            <a class="buttonred" href="#" onclick="event.preventDefault(); document.getElementById('delete-account-form').submit();">Удалить аккаунт</a>
        </div>

        
    </div>

    <!-- Правая колонка: Форма редактирования профиля -->
    <div class="profile__modules wow fadeInLeft" data-wow-duration="2s" data-wow-delay="2s">
        <div class=" profile__info__table">
            <div class="password__new" id="update-all-section">
                <h3>Личная информация</h3>
                <form id="update-all-form">
                    @csrf
                    <div class="row__column-profile">
                        <!-- Левая колонка -->
                        <div class="column-profile">
                            <div class="form-group-colum__profile">
                                <label for="name">Имя:</label>
                                <input type="text" id="name" name="name" value="{{ $user->name }}" placeholder="Имя и фамилия">
                            </div>
                            <div class="form-group-colum__profile">
                                <label for="email">Электронная почта:</label>
                                <input type="email" id="email" name="email" value="{{ $user->email }}" placeholder="example@mail.com">
                            </div>
                            <div class="form-group-colum__profile">
                                <label for="new_password">Новый пароль</label>
                                <input type="password" id="new_password" name="new_password" placeholder="********">
                            </div>
                            <div class="form-group-colum__profile">
                                <label for="new_password_confirmation">Подтверждение пароля</label>
                                <input type="password" id="new_password_confirmation" name="new_password_confirmation" placeholder="********">
                            </div>
                        </div>
            
                        <!-- Правая колонка -->
                        <div class="column">
                            @if($user->status == 'user')
                                <div class="form-group-colum__profile">
                                    <label for="city">Город:</label>
                                    <input type="text" id="city" name="city" value="{{ $user->city }}" placeholder="Ваш город">
                                </div>
                            @elseif($user->status == 'partner')
                                <div class="form-group-colum__profile">
                                    <label for="city">Город:</label>
                                    <input type="text" id="city" name="city" value="{{ $user->city }}" placeholder="Ваш город">
                                </div>
                                <div class="form-group-colum__profile">
                                    <label for="contract_number">Номер договора:</label>
                                    <input type="text" id="contract_number" name="contract_number" value="{{ $user->contract_number }}" placeholder="Номер договора">
                                </div>
                                <div class="form-group-colum__profile">
                                    <label for="comment">Комментарий:</label>
                                    <textarea id="comment" name="comment" placeholder="Дополнительная информация">{{ $user->comment }}</textarea>
                                </div>
                            @elseif($user->status == 'executor')
                                <div class="form-group-colum__profile">
                                    <label for="city">Город/Часовой пояс:</label>
                                    <input type="text" id="city" name="city" value="{{ $user->city }}" placeholder="Ваш город или часовой пояс">
                                </div>
                                <div class="form-group-colum__profile">
                                    <label for="portfolio_link">Ссылка на портфолио:</label>
                                    <input type="url" id="portfolio_link" name="portfolio_link" value="{{ $user->portfolio_link }}" placeholder="Ссылка на портфолио">
                                </div>
                                <div class="form-group-colum__profile">
                                    <label for="experience">Стаж:</label>
                                    <input type="text" id="experience" name="experience" value="{{ $user->experience }}" placeholder="Ваш стаж">
                                </div>
                                <div class="form-group-colum__profile">
                                    <label for="rating">Рейтинг в ЭД:</label>
                                    <input type="text" id="rating" name="rating" value="{{ $user->rating }}" placeholder="Ваш рейтинг">
                                </div>
                                <div class="form-group-colum__profile">
                                    <label for="active_projects_count">Проекты в работе:</label>
                                    <input type="number" id="active_projects_count" name="active_projects_count" value="{{ $user->active_projects_count }}" placeholder="Количество проектов">
                                </div>
                            @elseif($user->status == 'coordinator')
                                <div class="form-group-colum__profile">
                                    <label for="experience">Стаж:</label>
                                    <input type="text" id="experience" name="experience" value="{{ $user->experience }}" placeholder="Ваш стаж">
                                </div>
                                <div class="form-group-colum__profile">
                                    <label for="rating">Рейтинг в ЭД:</label>
                                    <input type="text" id="rating" name="rating" value="{{ $user->rating }}" placeholder="Ваш рейтинг">
                                </div>
                            @endif
                        </div>
                    </div>
                    <div id="error-message" style="color: red; display: none;"></div>
                    <button type="submit">Сохранить изменения</button>
                </form>
            </div>
            
        </div>
        
        <!-- Форма смены номера телефона -->
        <div class="profile__form">
            <div class="form-section" id="change-phone-section">
                <h3>Смена номера телефона</h3>
                <form id="change-phone-form">
                    <label for="new-phone">Новый номер телефона:</label>
                    <input type="text" id="new-phone" name="new-phone" placeholder="+7 (___) ___-____" required class="maskphone">
                    <button type="button" id="send-code">Отправить код</button>
                </form>
                <form id="verify-code-form" style="display: none;">
                    <label for="verification-code">Введите код подтверждения:</label>
                    <input type="text" id="verification-code" placeholder="Код из SMS" required>
                    <button type="button" id="confirm-code">Подтвердить</button>
                </form>
            </div>
        </div>
    </div>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<!-- Подключение jQuery и скрипты для AJAX -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Логика AJAX для смены номера телефона
$(document).ready(function() {
    $('#send-code').click(function() {
        var phone = $('#new-phone').val();
        $.ajax({
            url: '/profile/send-code',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                phone: phone
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    $('#change-phone-form').hide();
                    $('#verify-code-form').show();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Ошибка при отправке кода. Попробуйте позже.');
            }
        });
    });

    $('#confirm-code').click(function() {
        var phone = $('#new-phone').val();
        var code  = $('#verification-code').val();
        $.ajax({
            url: '/profile/verify-code',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                phone: phone,
                verification_code: code
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    $('#verify-code-form').hide();
                    $('#change-phone-form').show();
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Ошибка при проверке кода. Попробуйте позже.');
            }
        });
    });
});

// Логика AJAX для обновления профиля
$(document).ready(function() {
    $('#update-all-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: '{{ route("profile.update_all") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                } else {
                    $('#error-message').text(response.message).show();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessages = '';
                    for (const field in errors) {
                        errorMessages += errors[field].join(', ') + '<br>';
                    }
                    $('#error-message').html(errorMessages).show();
                } else {
                    $('#error-message').text('Произошла ошибка. Попробуйте позже.').show();
                }
            }
        });
    });
});

// Маска для ввода телефона
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
</script>

