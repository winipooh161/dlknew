<div class="modal modal__deal" id="editModal" style="display: none;">
    <div class="modal-content">
        @if(isset($deal) && isset($dealFields))
            <!-- Кнопка закрытия и навигация по модулям -->
            <div class="button__points">
                <span class="close-modal" id="closeModalBtn">&times;</span>
                <button data-target="Заказ" class="buttonSealaActive">Заказ</button>
                <button data-target="Работа над проектом">Работа над проектом</button>
                @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
                    <button data-target="Финал проекта">Финал проекта</button>
                @endif
                <button data-target="Лента">Лента</button>
                <ul>
                    <li>
                        <a href="#" onclick="event.preventDefault(); copyRegistrationLink('{{ $deal->registration_token ? route('register_by_deal', ['token' => $deal->registration_token]) : '#' }}')">
                            <img src="/storage/icon/link.svg" alt="Регистрационная ссылка">
                        </a>
                    </li>
                    @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
                        <li>
                            <a href="{{ route('deal.change_logs.deal', ['deal' => $deal->id]) }}">
                                <img src="/storage/icon/log.svg" alt="Логи">
                            </a>
                        </li>
                    @endif
                    <li>
                        @php
                            $groupChatUrl = isset($groupChat) && $groupChat ? url('/chats?active_chat=' . $groupChat->id) : '#';
                        @endphp
                        <a href="{{ $groupChatUrl }}">
                            <img src="/storage/icon/chat.svg" alt="Чат">
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Модуль ленты -->
            <fieldset class="module__deal" id="module-feed" style="display: none;">
                <legend>Лента</legend>
                <div class="feed-posts" id="feed-posts-container">
                    @foreach ($feeds as $feed)
                        <div class="feed-post">
                            <div class="feed-post-avatar">
                                <img src="{{ $feed->avatar_url ?? '/storage/group_default.svg' }}" alt="{{ $feed->user_name }}">
                            </div>
                            <div class="feed-post-text">
                                <div class="feed-author">{{ $feed->user_name }}</div>
                                <div class="feed-content">{{ $feed->content }}</div>
                                <div class="feed-date">{{ $feed->date }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <form id="feed-form" class="feed-form-post" action="#" method="POST">
                    @csrf
                    <input type="hidden" name="deal_id" value="{{ $deal->id }}">
                    <textarea id="feed-content" name="content" placeholder="Введите ваш комментарий" rows="3"></textarea>
                    <button type="submit">Отправить</button>
                </form>
            </fieldset>

            <!-- Форма редактирования сделки -->
            <form id="editForm" method="POST" enctype="multipart/form-data" action="{{ route('deal.update', $deal->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="deal_id" id="dealIdField" value="{{ $deal->id }}">
                @php
                    $userRole = Auth::user()->status;
                @endphp
                <!-- Модуль: Заказ -->
                <fieldset class="module__deal" id="module-zakaz">
                    <legend>Заказ</legend>
                    @foreach($dealFields['zakaz'] as $field)
                        <div class="form-group-deal">
                            <label>{{ $field['label'] }}:
                                @if($field['type'] == 'text')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['name'] == 'name' ? 'nameField' : ($field['id'] ?? $field['name']) }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                    @else
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['name'] == 'name' ? 'nameField' : ($field['id'] ?? $field['name']) }}" value="{{ $deal->{$field['name']} }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                    @endif
                                @elseif($field['type'] == 'select')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                            <option value="">-- Выберите значение --</option>
                                            @foreach($field['options'] as $value => $text)
                                                <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                    @endif
                                @elseif($field['type'] == 'textarea')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                    @else
                                        <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                    @endif
                                @elseif($field['type'] == 'file')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="file" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" accept="{{ isset($field['accept']) ? $field['accept'] : '' }}">
                                        @if(!empty($deal->{$field['name']}))
                                            <div class="file-link">
                                                <a href="{{ asset('storage/' . $deal->{$field['name']}) }}" target="_blank">Просмотр загруженного файла</a>
                                            </div>
                                        @endif
                                    @else
                                        @if(!empty($deal->{$field['name']}))
                                            <div class="file-link">
                                                <a href="{{ asset('storage/' . $deal->{$field['name']}) }}" target="_blank">Просмотр загруженного файла</a>
                                            </div>
                                        @endif
                                    @endif
                                @elseif($field['type'] == 'date')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                                    @else
                                        <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                    @endif
                                @elseif($field['type'] == 'number')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" step="{{ isset($field['step']) ? $field['step'] : '0.01' }}">
                                    @else
                                        <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                    @endif
                                @endif
                            </label>
                        </div>
                    @endforeach
                </fieldset>
    
                <!-- Модуль: Работа над проектом -->
                <fieldset class="module__deal" id="module-rabota">
                    <legend>Работа над проектом</legend>
                    @foreach($dealFields['rabota'] as $field)
                        <div class="form-group-deal">
                            <label>{{ $field['label'] }}:
                                @if($field['type'] == 'text')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['name'] == 'name' ? 'nameField' : ($field['id'] ?? $field['name']) }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                    @else
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['name'] == 'name' ? 'nameField' : ($field['id'] ?? $field['name']) }}" value="{{ $deal->{$field['name']} }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                    @endif
                                @elseif($field['type'] == 'select')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                            <option value="">-- Выберите значение --</option>
                                            @foreach($field['options'] as $value => $text)
                                                <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                    @endif
                                @elseif($field['type'] == 'textarea')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                    @else
                                        <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                    @endif
                                @elseif($field['type'] == 'file')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="file" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" accept="{{ isset($field['accept']) ? $field['accept'] : '' }}">
                                        @if(!empty($deal->{$field['name']}))
                                            <div class="file-link">
                                                <a href="{{ asset('storage/' . $deal->{$field['name']}) }}" target="_blank">Просмотр загруженного файла</a>
                                            </div>
                                        @endif
                                    @else
                                        @if(!empty($deal->{$field['name']}))
                                            <div class="file-link">
                                                <a href="{{ asset('storage/' . $deal->{$field['name']}) }}" target="_blank">Просмотр загруженного файла</a>
                                            </div>
                                        @endif
                                    @endif
                                @elseif($field['type'] == 'date')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                                    @else
                                        <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                    @endif
                                @elseif($field['type'] == 'number')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" step="{{ isset($field['step']) ? $field['step'] : '0.01' }}">
                                    @else
                                        <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                    @endif
                                @endif
                            </label>
                        </div>
                    @endforeach
                </fieldset>
    
                <!-- Модуль Финал проекта только для координаторов и администраторов -->
                @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
                    <!-- Модуль: Финал проекта -->
                    <fieldset class="module__deal" id="module-final">
                        <legend>Финал проекта</legend>
                        @foreach($dealFields['final'] as $field)
                            <div class="form-group-deal">
                                <label>{{ $field['label'] }}:
                                    @if($field['type'] == 'text')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="text" name="{{ $field['name'] }}" id="{{ $field['name'] == 'name' ? 'nameField' : ($field['id'] ?? $field['name']) }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                        @else
                                            <input type="text" name="{{ $field['name'] }}" id="{{ $field['name'] == 'name' ? 'nameField' : ($field['id'] ?? $field['name']) }}" value="{{ $deal->{$field['name']} }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                        @endif
                                    @elseif($field['type'] == 'select')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                                <option value="">-- Выберите значение --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                        @endif
                                    @elseif($field['type'] == 'textarea')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                        @else
                                            <textarea name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>{{ $deal->{$field['name']} }}</textarea>
                                        @endif
                                    @elseif($field['type'] == 'file')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="file" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" accept="{{ isset($field['accept']) ? $field['accept'] : '' }}">
                                            @if(!empty($deal->{$field['name']}))
                                                <div class="file-link">
                                                    <a href="{{ asset('storage/' . $deal->{$field['name']}) }}" target="_blank">Просмотр загруженного файла</a>
                                                </div>
                                            @endif
                                        @else
                                            @if(!empty($deal->{$field['name']}))
                                                <div class="file-link">
                                                    <a href="{{ asset('storage/' . $deal->{$field['name']}) }}" target="_blank">Просмотр загруженного файла</a>
                                                </div>
                                            @endif
                                        @endif
                                    @elseif($field['type'] == 'date')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                                        @else
                                            <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                        @endif
                                    @elseif($field['type'] == 'number')
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" step="{{ isset($field['step']) ? $field['step'] : '0.01' }}">
                                        @else
                                            <input type="number" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                        @endif
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </fieldset>
                @endif
                <div class="form-buttons">
                    <button type="submit" id="saveButton">Сохранить</button>
                </div>
            </form>
        @endif
    </div>
</div>

<!-- Скрипты для работы модального окна (можно вынести в отдельный JS-файл) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Пример функции копирования регистрационной ссылки
    window.copyRegistrationLink = function(regUrl) {
        if (regUrl && regUrl !== '#') {
            navigator.clipboard.writeText(regUrl).then(function() {
                alert('Регистрационная ссылка скопирована: ' + regUrl);
            }).catch(function(err) {
                console.error('Ошибка копирования ссылки: ', err);
            });
        } else {
            alert('Регистрационная ссылка отсутствует.');
        }
    };

    // Пример переключения модулей
    var modules = document.querySelectorAll("#editModal fieldset.module__deal");
    var buttons = document.querySelectorAll("#editModal .button__points button");

    modules.forEach(function(module) {
        module.style.display = "none";
        module.style.opacity = "0";
        module.style.transition = "opacity 0.3s ease-in-out";
    });
    // По умолчанию показываем модуль "Заказ"
    var defaultModule = document.querySelector("#module-zakaz");
    if(defaultModule){
        defaultModule.style.display = "flex";
        setTimeout(function(){ defaultModule.style.opacity = "1"; }, 10);
    }

    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetText = btn.getAttribute('data-target').trim();
            buttons.forEach(function(b){ b.classList.remove("buttonSealaActive"); });
            btn.classList.add("buttonSealaActive");

            modules.forEach(function(module) {
                module.style.opacity = "0";
                setTimeout(function(){ module.style.display = "none"; }, 300);
            });
            setTimeout(function(){
                modules.forEach(function(module){
                    if(module.querySelector("legend") && module.querySelector("legend").innerText.trim() === targetText){
                        module.style.display = "flex";
                        setTimeout(function(){ module.style.opacity = "1"; }, 10);
                    }
                });
            }, 300);
        });
    });

    // Закрытие модального окна
    document.getElementById('closeModalBtn').addEventListener('click', function(){
        document.getElementById('editModal').style.display = "none";
    });
});
</script>
