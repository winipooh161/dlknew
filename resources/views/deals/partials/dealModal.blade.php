<div class="modal modal__deal" id="editModal" style="display: none;">
    <div class="modal-content">
        @if(isset($deal) && isset($dealFields))
        <!-- Кнопка закрытия -->
     
        <div class="button__points">
            <span class="close-modal" id="closeModalBtn">&times;</span>
            <button data-target="Заказ">Заказ</button>
            <button data-target="Работа над проектом">Работа над проектом</button>
            @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
                <button data-target="Финал проекта">Финал проекта</button>
            @endif
            <button data-target="Лента" class="active-button">Лента</button>
            <ul>
                <li>
                    <a href="#" onclick="event.preventDefault(); copyRegistrationLink('{{ $deal->registration_token ? route('register_by_deal', ['token' => $deal->registration_token]) : '#' }}')">
                        <img src="/storage/icon/link.svg" alt="Регистрационная ссылка">
                    </a>
                </li>
                <script>
                     function copyRegistrationLink(regUrl) {
        if (regUrl) {
            navigator.clipboard.writeText(regUrl).then(function() {
                alert('Регистрационная ссылка скопирована: ' + regUrl);
            }).catch(function(err) {
                console.error('Ошибка копирования ссылки: ', err);
            });
        } else {
            alert('Регистрационная ссылка отсутствует.');
        }
    }
                </script>
                @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
                    <li>
                        <a href="{{ route('deal.change_logs.deal', ['deal' => $deal->id]) }}">
                            <img src="/storage/icon/log.svg" alt="Логи">
                        </a>
                    </li>
                @endif
                <li>
                    <a href="{{ $groupChat ? url('/chats?active_chat=' . $groupChat->id) : '#' }}">
                        <img src="/storage/icon/chat.svg" alt="Чат">
                    </a>
                </li>
            </ul>
        </div>
        <!-- Модуль: Лента -->
        <fieldset class="module__deal" style="display: none" id="module-feed">
            <legend>Лента</legend>
            <div class="feed-posts" id="feed-posts-container">
                @foreach ($feeds as $feed)
                    <div class="feed-post">
                        <div class="feed-post-avatar">
                            <img src="{{ $feed->avatar_url ?? '/storage/group_default.svg' }}"
                                alt="{{ $feed->user_name }}">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработчик отправки формы с улучшенной обработкой ответа AJAX
    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Валидация на клиенте (пример)
        var nameField = document.getElementById('nameField');
        if (!nameField.value.trim()) {
            alert('Пожалуйста, введите ФИО клиента.');
            nameField.focus();
            return;
        }

        const formData = new FormData(this);
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Ошибка сервера: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Успешно:', data);
            alert('Данные успешно сохранены!');
            $("#editModal").removeClass('show').hide();
            // ...existing code для дополнительной логики...
        })
        .catch(error => {
            console.error('Ошибка при отправке формы:', error);
            alert('Произошла ошибка при сохранении данных: ' + error.message);
        });
    });

    var modules = $("#editModal fieldset.module__deal");
    var buttons = $("#editModal .button__points button");
    
    modules.css({
        display: "none",
        opacity: "0",
        transition: "opacity 0.3s ease-in-out"
    });
    
    // По умолчанию открываем модуль "Заказ" через имитацию клика
    $("#editModal .button__points button[data-target='Заказ']").trigger('click');
    
    // Остальной код переключения остается без изменений...
    buttons.on('click', function() {
        var targetText = $(this).data('target').trim();
        buttons.removeClass("buttonSealaActive");
        $(this).addClass("buttonSealaActive");
        
        modules.css({
            opacity: "0"
        });
        
        setTimeout(function() {
            modules.css({
                display: "none"
            });
        }, 300);
        
        setTimeout(function() {
            modules.each(function() {
                var legend = $(this).find("legend").text().trim();
                if (legend === targetText) {
                    $(this).css({
                        display: "flex"
                    });
                    setTimeout(function() {
                        $(this).css({
                            opacity: "1"
                        });
                    }.bind(this), 10);
                }
            });
        }, 300);
    });
});
</script>

</div>
    <!-- Modal content -->
        <span class="close">&times;</span>
        <p>Текст для модального окна.</p>
        <!-- Пример ссылки с передачей параметра token -->
        <a href="{{ route('register_by_deal', ['token' => $deal->token ?? 'missing_token']) }}">Зарегистрироваться по сделке</a>
    </div>