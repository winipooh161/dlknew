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
                <ul>
                    <li>
                        <a href="#" onclick="event.preventDefault(); copyRegistrationLink('{{ $deal->registration_token ? route('register_by_deal', ['token' => $deal->registration_token]) : '#' }}')" title="Скопировать регистрационную ссылку для клиента">
                            <img src="/storage/icon/link.svg" alt="Регистрационная ссылка">
                        </a>
                    </li>
                    @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
                        <li>
                            <a href="{{ route('deal.change_logs.deal', ['deal' => $deal->id]) }}" title="Просмотр истории изменений сделки">
                                <img src="/storage/icon/log.svg" alt="Логи">
                            </a>
                        </li>
                    @endif
                    @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                        <li>
                            <a href="{{ $deal->link ? url($deal->link) : '#' }}" title="Открыть бриф клиента">
                                <img src="/storage/icon/brif.svg" alt="Бриф клиента">
                            </a>
                        </li>
                    @endif
                    <li>
                        @php
                            $groupChatUrl = isset($groupChat) && $groupChat ? url('/chats?active_chat=' . $groupChat->id) : '#';
                        @endphp
                        <a href="{{ $groupChatUrl }}" title="Перейти в групповой чат сделки">
                            <img src="/storage/icon/chat.svg" alt="Чат">
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Добавляем раздел для группового чата, если он существует --}}
            @if(isset($groupChat) && $groupChat)
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title">Групповой чат сделки</h5>
                </div>
                <div class="card-body">
                    <p><strong>Название:</strong> {{ $groupChat->name }}</p>
                    <p><strong>Участники:</strong> {{ $groupChat->users()->count() }}</p>
                    <div class="mt-2">
                        <a href="{{ route('chat') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-chat-dots"></i> Перейти в чат
                        </a>
                    </div>
                </div>
            </div>
            @endif
         
            <!-- Форма редактирования сделки -->
            <form id="editForm" method="POST" enctype="multipart/form-data" action="{{ route('deal.update', $deal->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="deal_id" id="dealIdField" value="{{ $deal->id }}">
                @php
                    $userRole = Auth::user()->status;
                @endphp
                <!-- Модуль: Заказ -->
                <fieldset class="module__deal" id="module-zakaz"style="display: flex;"> 
                    <legend>Заказ</legend>
                    @foreach($dealFields['zakaz'] as $field)
                        <div class="form-group-deal">
                            <label>{{ $field['label'] }}:
                                @if($field['name'] == 'client_city')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <select name="{{ $field['name'] }}" id="client_city" class="select2-field">
                                            <option value="">-- Выберите город --</option>
                                            @if(!empty($deal->client_city))
                                                <option value="{{ $deal->client_city }}" selected>{{ $deal->client_city }}</option>
                                            @endif
                                        </select>
                                    @else
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                    @endif
                                @elseif($field['type'] == 'text')
                                    @if(isset($field['role']) && in_array($userRole, $field['role']))
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['name'] == 'name' ? 'nameField' : ($field['id'] ?? $field['name']) }}" value="{{ $deal->{$field['name']} }}" {{ isset($field['required']) && $field['required'] ? 'required' : '' }} {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                    @else
                                        <input type="text" name="{{ $field['name'] }}" id="{{ $field['name'] == 'name' ? 'nameField' : ($field['id'] ?? $field['name']) }}" value="{{ $deal->{$field['name']} }}" disabled {{ isset($field['maxlength']) ? 'maxlength='.$field['maxlength'] : '' }}>
                                    @endif
                                @elseif($field['type'] == 'select')
                                    <!-- Для поля координатора - особая обработка -->
                                    @if($field['name'] == 'coordinator_id')
                                        @if(Auth::user()->status == 'partner')
                                            <!-- Партнер может только видеть поле координатора -->
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled>
                                                <option value="">-- Выберите координатора --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <!-- Администраторы и координаторы могут изменять -->
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                                <option value="">-- Выберите координатора --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    @else
                                        <!-- Стандартное отображение для других полей select -->
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
                                    @if($field['name'] == 'created_date')
                                        @if(in_array($userRole, ['coordinator', 'admin']))
                                            <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                                        @else
                                            <p class="deal-date-display">{{ \Carbon\Carbon::parse($deal->{$field['name']})->format('d.m.Y') }}</p>
                                        @endif
                                    @else
                                        @if(isset($field['role']) && in_array($userRole, $field['role']))
                                            <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}">
                                        @else
                                            <input type="date" name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" value="{{ $deal->{$field['name']} }}" disabled>
                                        @endif
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
                                    <!-- Для поля координатора - особая обработка -->
                                    @if($field['name'] == 'coordinator_id')
                                        @if(Auth::user()->status == 'partner')
                                            <!-- Партнер может только видеть поле координатора -->
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled>
                                                <option value="">-- Выберите координатора --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <!-- Администраторы и координаторы могут изменять -->
                                            <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                                <option value="">-- Выберите координатора --</option>
                                                @foreach($field['options'] as $value => $text)
                                                    <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    @else
                                        <!-- Стандартное отображение для других полей select -->
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
                                        <!-- Для поля координатора - особая обработка -->
                                        @if($field['name'] == 'coordinator_id')
                                            @if(Auth::user()->status == 'partner')
                                                <!-- Партнер может только видеть поле координатора -->
                                                <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}" disabled>
                                                    <option value="">-- Выберите координатора --</option>
                                                    @foreach($field['options'] as $value => $text)
                                                        <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <!-- Администраторы и координаторы могут изменять -->
                                                <select name="{{ $field['name'] }}" id="{{ $field['id'] ?? $field['name'] }}">
                                                    <option value="">-- Выберите координатора --</option>
                                                    @foreach($field['options'] as $value => $text)
                                                        <option value="{{ $value }}" {{ $deal->{$field['name']} == $value ? 'selected' : '' }}>{{ $text }}</option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        @else
                                            <!-- Стандартное отображение для других полей select -->
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
        // Функция копирования регистрационной ссылки
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
        
        // Загрузка городов из JSON-файла для селекта
        if(document.getElementById('client_city')) {
            // Проверяем наличие jQuery перед использованием
            if (typeof $ === 'undefined') {
                var script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js';
                script.onload = initializeSelect2;
                document.head.appendChild(script);
            } else {
                initializeSelect2();
            }
            
            function initializeSelect2() {
                // Проверяем наличие Select2 перед использованием
                if (typeof $.fn.select2 === 'undefined') {
                    var linkElement = document.createElement('link');
                    linkElement.rel = 'stylesheet';
                    linkElement.href = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css';
                    document.head.appendChild(linkElement);
                    
                    var script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js';
                    script.onload = loadCitiesAndInitSelect2;
                    document.head.appendChild(script);
                } else {
                    loadCitiesAndInitSelect2();
                }
            }
            
            function loadCitiesAndInitSelect2() {
                $.getJSON('/public/cities.json', function(data) {
                    // Группируем города по региону
                    var groupedOptions = {};
                    data.forEach(function(item) {
                        var region = item.region;
                        var city = item.city;
                        if (!groupedOptions[region]) {
                            groupedOptions[region] = [];
                        }
                        // Форматируем данные для Select2
                        groupedOptions[region].push({
                            id: city,
                            text: city
                        });
                    });

                    // Преобразуем сгруппированные данные в массив для Select2
                    var select2Data = [];
                    for (var region in groupedOptions) {
                        select2Data.push({
                            text: region,
                            children: groupedOptions[region]
                        });
                    }

                    // Инициализируем Select2 с полученными данными и настройками доступности
                    $('#client_city').select2({
                        data: select2Data,
                        placeholder: "-- Выберите город --",
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#editModal'),
                        // Добавляем обработчики для доступности
                        closeOnSelect: true
                    }).on('select2:open', function() {
                        // Исправление проблемы aria-hidden
                        document.querySelector('.select2-container--open').setAttribute('inert', 'false');
                        
                        // Удаляем aria-hidden с dropdown контейнера и родителей
                        setTimeout(function() {
                            var dropdowns = document.querySelectorAll('.select2-dropdown');
                            dropdowns.forEach(function(dropdown) {
                                var parent = dropdown.parentElement;
                                while (parent) {
                                    if (parent.hasAttribute('aria-hidden')) {
                                        parent.removeAttribute('aria-hidden');
                                    }
                                    parent = parent.parentElement;
                                }
                            });
                            
                            // Дополнительно для модального окна
                            if (document.querySelector('.modal[aria-hidden="true"]')) {
                                document.querySelector('.modal[aria-hidden="true"]').removeAttribute('aria-hidden');
                            }
                        }, 10);
                    });
                    
                    // Если город уже был выбран, устанавливаем его
                    var currentCity = "{{ $deal->client_city ?? '' }}";
                    if(currentCity) {
                        // Создаем новый элемент option и добавляем к select
                        if($("#client_city").find("option[value='" + currentCity + "']").length === 0) {
                            var newOption = new Option(currentCity, currentCity, true, true);
                            $('#client_city').append(newOption);
                        }
                        $('#client_city').val(currentCity).trigger('change');
                    }
                })
                .fail(function(jqxhr, textStatus, error) {
                    console.error("Ошибка загрузки JSON файла городов: " + textStatus + ", " + error);
                    // Добавляем резервное решение при ошибке загрузки JSON
                    var currentCity = "{{ $deal->client_city ?? '' }}";                    if(currentCity) {                        var option = new Option(currentCity, currentCity, true, true);                        $('#client_city').append(option).trigger('change');
                    }
                });
            }
        }
        
        // Обработчик закрытия модального окна - убираем возможные остатки aria-hidden
        document.getElementById('closeModalBtn').addEventListener('click', function() {
            // Очистка атрибутов aria-hidden при закрытии
            var modal = document.getElementById('editModal');
            if (modal.hasAttribute('aria-hidden')) {
                modal.removeAttribute('aria-hidden');
            }
        });
    });
</script>

<!-- Подключаем Select2 и добавляем CSS стили для исправления проблемы с aria-hidden -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<style>
    /* Стили для исправления проблемы с aria-hidden */
    body .select2-container--open {
        z-index: 10000 !important;
    }
    
    body .select2-dropdown {
        z-index: 10001 !important;
    }
    
    /* Фиксированное позиционирование для выпадающего списка в модальном окне */
    #editModal .select2-dropdown {
        position: fixed !important;
    }
    
    /* Устанавливаем правильное отображение в модальном окне */
    .modal-content .select2-container {
        width: 100% !important;
    }
    
    /* Предотвращаем скрытие Select2 из-за aria-hidden */
    .select2-hidden-accessible {
        border: 0 !important;
        clip: rect(0 0 0 0) !important;
        height: 1px !important;
        margin: -1px !important;
        overflow: hidden !important;
        padding: 0 !important;
        position: absolute !important;
        width: 1px !important;
    }
</style>
