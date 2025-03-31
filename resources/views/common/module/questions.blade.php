@if (!empty($title) || !empty($subtitle))
    <div class="form__title" id="top-title">
        <div class="form__title__info">
            @if (!empty($title))
                <h1>{{ $title }}</h1>
            @endif
            @if (!empty($subtitle))
                <p>{{ $subtitle }}</p>
            @endif
        </div>
        {{-- Кнопки навигации --}}
        <div class="form__button flex between">
            <p>Страница {{ $page }}/{{ $totalPages }}</p>
            @if ($page > 1)
                <button type="button" class="btn btn-secondary" onclick="goToPrev()">Обратно</button>
            @endif
            <button type="button" class="btn btn-primary" onclick="validateAndSubmit()">Далее</button>
            
            @if ($page > 0 && $page < 15)
                <button type="button" class="btn btn-warning" onclick="skipPage()">Пропустить</button>
            @endif
            
            @if ($page >= 15 && !empty(json_decode($brif->skipped_pages ?? '[]')))
                <span class="skipped-notice">Вы заполняете пропущенные страницы</span>
            @endif
        </div>
    </div>
@endif

<form id="briefForm" action="{{ route('common.saveAnswers', ['id' => $brif->id, 'page' => $page]) }}" method="POST"
    enctype="multipart/form-data" class="back__fon__common">
    @csrf
    <!-- Скрытое поле для определения направления перехода -->
    <input type="hidden" name="action" id="actionInput" value="next">
    <!-- Скрытое поле для определения, была ли страница пропущена -->
    <input type="hidden" name="skip_page" id="skipPageInput" value="0">

    <!-- Добавляем стили для ошибок валидации -->
    <style>
        .field-error {
            border: 2px solid #ff0000 !important;
            background-color: #fff0f0 !important;
        }
        
        .error-message {
            color: #ff0000;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }
        
        .error-placeholder::placeholder {
            color: #ff0000 !important;
            opacity: 1;
        }
    </style>

    @if($page == 0)
        <div class="form__body flex between wrap pointblock">
            {{-- Используем $questions для вывода чекбоксов комнат --}}
            @foreach($questions as $room)
                <div class="checkpoint flex wrap">
                    <div class="radio">
                        <input type="checkbox" id="room_{{ $room['key'] }}" class="custom-checkbox"
                               name="answers[{{ $room['key'] }}]" value="{{ $room['title'] }}"
                               @if(isset($brif->{$room['key']})) checked @endif>
                        <label for="room_{{ $room['key'] }}">{{ $room['title'] }}</label>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="form__button flex between">
            @if($page > 0)
                <button type="button" onclick="window.location.href='{{ route('common.questions', ['id' => $brif->id, 'page' => $page - 1]) }}'">Назад</button>
            @else
                <button type="button" onclick="window.location.href='{{ route('brifs.index') }}'">Отмена</button>
            @endif
            
            <button type="submit">Далее</button>
            
            @if($page < 15)
                <button type="button" class="skip-button" onclick="skipPage()">Пропустить</button>
            @endif
        </div>
    @endif

    {{-- Блок с вопросами форматов "default" и "faq" --}}
    <div class="form__body flex between wrap">
        @foreach ($questions as $question)
            @if ($question['format'] === 'default')
                <div class="form-group flex wrap">
                    <h2>{{ $question['title'] }}</h2>
                    @if (!empty($question['subtitle']))
                        <p>{{ $question['subtitle'] }}</p>
                    @endif
                    @if ($question['type'] === 'textarea')
                        <textarea name="answers[{{ $question['key'] }}]" placeholder="{{ $question['placeholder'] }}" 
                            class="form-control required-field" data-original-placeholder="{{ $question['placeholder'] }}"
                            maxlength="500">{{ $brif->{$question['key']} ?? '' }}</textarea>
                    @else
                        <input type="text" name="answers[{{ $question['key'] }}]" class="form-control required-field"
                            value="{{ $brif->{$question['key']} ?? '' }}" placeholder="{{ $question['placeholder'] }}"
                            data-original-placeholder="{{ $question['placeholder'] }}" maxlength="500">
                    @endif
                    <span class="error-message">Это поле обязательно для заполнения</span>
                </div>
            @endif

            {{-- Если формат faq — аккордеон --}}
            @if ($question['format'] === 'faq')
                <div class="faq__body">
                    <div class="faq_block flex center">
                        <div class="faq_item">
                            <div class="faq_question" onclick="toggleFaq(this)">
                                <h2>{{ $question['title'] }}</h2>
                                <svg class="arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    width="24" height="24">
                                    <path d="M7 10l5 5 5-5z"></path>
                                </svg>
                            </div>
                            <div class="faq_answer">
                                @if ($question['type'] === 'textarea')
                                    <textarea name="answers[{{ $question['key'] }}]" placeholder="{{ $question['placeholder'] }}" 
                                        class="form-control required-field" data-original-placeholder="{{ $question['placeholder'] }}"
                                        maxlength="500">{{ $brif->{$question['key']} ?? '' }}</textarea>
                                @else
                                    <input type="text" name="answers[{{ $question['key'] }}]" class="form-control required-field"
                                        value="{{ $brif->{$question['key']} ?? '' }}" placeholder="{{ $question['placeholder'] }}"
                                        data-original-placeholder="{{ $question['placeholder'] }}" maxlength="500">
                                @endif
                                <span class="error-message">Это поле обязательно для заполнения</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Если формат checkpoint — чекбоксы --}}
            @if ($question['format'] === 'checkpoint')
                <div class="checkpoint flex wrap">
                    <div class="radio">
                        <input type="checkbox" id="{{ $question['key'] }}" class="custom-checkbox"
                               name="answers[{{ $question['key'] }}]" value="1"
                               @if(isset($brif->{$question['key']}) && $brif->{$question['key']} == 1) checked @endif>
                        <label for="{{ $question['key'] }}">{{ $question['title'] }}</label>
                    </div>
                </div>
            @endif
        @endforeach

        {{-- Если это страница 15 — загрузка файлов --}}
        @if ($page == 15)
            <div class="upload__files">
                <h6>Загрузите документы (не более 25 МБ суммарно):</h6>
                <div id="drop-zone">
                    <p id="drop-zone-text">Перетащите файлы сюда или нажмите, чтобы выбрать</p>
                    <input id="fileInput" type="file" name="documents[]" multiple
                        accept=".pdf,.xlsx,.xls,.doc,.docx,.jpg,.jpeg,.png,.heic,.heif">
                </div>
                <p class="error-message" style="color: red;"></p>
                <small>Допустимые форматы: .pdf, .xlsx, .xls, .doc, .docx, .jpg, .jpeg, .png, .heic, .heif</small><br>
                <small>Максимальный суммарный размер: 25 МБ</small>
            </div>

            <style>
                .upload__files {
                    margin: 20px 0;
                    font-family: Arial, sans-serif;
                }

                /* Стилизация области перетаскивания */
                #drop-zone {
                    border: 2px dashed #ccc;
                    border-radius: 6px;
                    padding: 30px;
                    text-align: center;
                    cursor: pointer;
                    position: relative;
                    transition: background-color 0.3s ease;
                }

                #drop-zone.dragover {
                    background-color: #f0f8ff;
                    border-color: #007bff;
                }

                #drop-zone p {
                    margin: 0;
                    font-size: 16px;
                    color: #666;
                }

                /* Скрываем нативное поле выбора файлов, но оставляем его доступным */
                #fileInput {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    opacity: 0;
                    cursor: pointer;
                }
            </style>

            <script>
                const dropZone = document.getElementById('drop-zone');
                const fileInput = document.getElementById('fileInput');
                const dropZoneText = document.getElementById('drop-zone-text');

                // Функция обновления текста в drop zone
                function updateDropZoneText() {
                    const files = fileInput.files;
                    if (files && files.length > 0) {
                        const names = [];
                        for (let i = 0; i < files.length; i++) {
                            names.push(files[i].name);
                        }
                        dropZoneText.textContent = names.join(', ');
                    } else {
                        dropZoneText.textContent = "Перетащите файлы сюда или нажмите, чтобы выбрать";
                    }
                }

                // Предотвращаем поведение по умолчанию для событий drag-and-drop
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }, false);
                });

                // Добавляем класс при перетаскивании
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, () => {
                        dropZone.classList.add('dragover');
                    }, false);
                });

                // Удаляем класс, когда файлы покидают область или сброшены
                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, () => {
                        dropZone.classList.remove('dragover');
                    }, false);
                });

                // Обработка события сброса (drop)
                dropZone.addEventListener('drop', function(e) {
                    let files = e.dataTransfer.files;
                    fileInput.files = files;
                    updateDropZoneText();
                });

                // При изменении поля выбора файлов обновляем текст
                fileInput.addEventListener('change', function() {
                    updateDropZoneText();
                });
            </script>
        @endif

    </div>

    {{-- Если это страница 14 — вычисление бюджета --}}
    @if ($page == 14)
        <div class="faq__custom-template__prise">
            <h6>Бюджет: <span id="budget-total">0</span></h6>
            <input type="hidden" id="budget-input" name="price" value="0">
        </div>

        <script>
            function calculateBudget() {
                let total = 0;
                const textareas = document.querySelectorAll('.faq__body textarea');
                textareas.forEach(function(textarea) {
                    const value = parseFloat(textarea.value.replace(/\D/g, '')) || 0;
                    if (value !== 0) {
                        total += value;
                    }
                });
                document.getElementById('budget-total').textContent = formatCurrency(total);
                document.getElementById('budget-input').value = total;
            }

            function formatCurrency(amount) {
                return amount.toLocaleString('ru-RU') + '₽';
            }

            function formatTextareaInput(event) {
                let value = event.target.value.replace(/\D/g, '');
                if (value) {
                    value = parseInt(value, 10).toLocaleString('ru-RU');
                }
                event.target.value = value + '₽';
            }

            document.querySelectorAll('.faq__body textarea').forEach(function(textarea) {
                textarea.addEventListener('input', function(e) {
                    formatTextareaInput(e);
                    calculateBudget();
                });
            });

            window.addEventListener('load', calculateBudget);
        </script>
    @endif
</form>

<!-- JavaScript для проверки заполнения обязательных полей и возможности пропуска страниц -->
<script>
    // Функция для проверки заполнения всех обязательных полей
    function validateForm() {
        let isValid = true;
        const requiredFields = document.querySelectorAll('.required-field');
        
        // Сбрасываем стили ошибок для всех полей
        requiredFields.forEach(function(field) {
            field.classList.remove('field-error', 'error-placeholder');
            field.placeholder = field.getAttribute('data-original-placeholder');
            
            const errorMsg = field.nextElementSibling;
            if (errorMsg && errorMsg.classList.contains('error-message')) {
                errorMsg.style.display = 'none';
            }
        });
        
        // Проверяем каждое обязательное поле
        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                isValid = false;
                
                // Добавляем стили ошибок
                field.classList.add('field-error', 'error-placeholder');
                field.placeholder = 'Заполните это поле!';
                
                // Показываем сообщение об ошибке
                const errorMsg = field.nextElementSibling;
                if (errorMsg && errorMsg.classList.contains('error-message')) {
                    errorMsg.style.display = 'block';
                }
                
                // Если поле в аккордеоне, открываем аккордеон
                const faqItem = field.closest('.faq_item');
                if (faqItem && !faqItem.classList.contains('active')) {
                    toggleFaq(faqItem.querySelector('.faq_question'));
                }
            }
        });
        
        return isValid;
    }
    
    // Функция для отправки формы после валидации
    function validateAndSubmit() {
        if (validateForm()) {
            document.getElementById('actionInput').value = 'next';
            document.getElementById('skipPageInput').value = '0';
            document.getElementById('briefForm').submit();
        }
    }
    
    // Функция для пропуска текущей страницы
    function skipPage() {
        // Проверяем, что страница < 15, так как страницы 15+ нельзя пропускать
        @if ($page < 15)
            // Создаем форму CSRF-токена для отправки
            const csrfToken = '{{ csrf_token() }}';
            
            // Отправляем запрос на пропуск текущей страницы
            fetch('{{ route('common.skipPage', ['id' => $brif->id, 'page' => $page]) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin' // Важно для работы с сессиями и куками
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка сервера: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Произошла ошибка при пропуске страницы');
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при пропуске страницы. Пожалуйста, попробуйте еще раз.');
            });
        @else
            alert('Эту страницу нельзя пропустить.');
        @endif
    }
    
    // Функция для перехода на предыдущую страницу
    function goToPrev() {
        document.getElementById('actionInput').value = 'prev';
        document.getElementById('briefForm').submit();
    }
    
    // Функция для переключения аккордеонов FAQ
    function toggleFaq(questionElement) {
        const faqItem = questionElement.parentElement;
        const faqAnswer = faqItem.querySelector('.faq_answer');
        const inputElement = faqAnswer.querySelector('textarea, input');
        const isActive = faqItem.classList.contains('active');

        if (!isActive) {
            faqItem.classList.add('active');
            faqAnswer.style.height = '0px';
            faqAnswer.offsetHeight; // принудительный reflow
            faqAnswer.style.height = faqAnswer.scrollHeight + 'px';
            if (inputElement) {
                setTimeout(() => {
                    inputElement.focus();
                }, 50);
            }
        } else {
            faqItem.classList.remove('active');
            const currentHeight = faqAnswer.scrollHeight;
            faqAnswer.style.height = currentHeight + 'px';
            faqAnswer.offsetHeight;
            faqAnswer.style.height = '0px';
        }
    }
    
    // Добавляем обработчики событий для полей, чтобы убирать ошибки при вводе
    document.addEventListener('DOMContentLoaded', function() {
        const requiredFields = document.querySelectorAll('.required-field');
        
        requiredFields.forEach(function(field) {
            field.addEventListener('input', function() {
                if (field.value.trim()) {
                    field.classList.remove('field-error', 'error-placeholder');
                    field.placeholder = field.getAttribute('data-original-placeholder');
                    
                    const errorMsg = field.nextElementSibling;
                    if (errorMsg && errorMsg.classList.contains('error-message')) {
                        errorMsg.style.display = 'none';
                    }
                }
            });
        });
    });
</script>

<!-- Скрипт для проверки файлов на размер и формат -->
<script>
    document.getElementById('fileInput')?.addEventListener('change', function() {
        const allowedFormats = ['pdf', 'xlsx', 'xls', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'heic', 'heif'];
        const errorMessageElement = document.querySelector('.error-message');
        const files = this.files;
        let totalSize = 0;
        errorMessageElement.textContent = '';
        for (const file of files) {
            const fileExt = file.name.split('.').pop().toLowerCase();
            if (!allowedFormats.includes(fileExt)) {
                errorMessageElement.textContent = `Недопустимый формат файла: ${file.name}.`;
                this.value = '';
                return;
            }
            totalSize += file.size;
        }
        if (totalSize > 25 * 1024 * 1024) {
            errorMessageElement.textContent = 'Суммарный размер файлов не должен превышать 25 МБ.';
            this.value = '';
        }
    });
</script>

<style>
    .skipped-notice {
        color: #ff6600;
        font-weight: bold;
        font-size: 14px;
        padding: 5px 10px;
        background-color: #fff3e0;
        border-radius: 4px;
        border: 1px solid #ff9800;
    }
</style>
