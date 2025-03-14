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
            <button type="button" class="btn btn-primary" onclick="goToNext()">Далее</button>
        </div>
        
    </div>
@endif

<form id="briefForm" action="{{ route('common.saveAnswers', ['id' => $brif->id, 'page' => $page]) }}" method="POST" enctype="multipart/form-data" class="back__fon__common">
    @csrf
    <!-- Скрытое поле для определения направления перехода -->
    <input type="hidden" name="action" id="actionInput" value="next">

    @php
    $checkpointQuestions = collect($questions)->where('format', 'checkpoint');
@endphp

@if($checkpointQuestions->isNotEmpty())
    <div class="form__body flex between wrap pointblock">
        @foreach ($checkpointQuestions as $question)
            <div class="checkpoint flex wrap">
                <div class="radio">
                    <input type="checkbox"
                           id="answer_{{ $loop->index }}"
                           class="custom-checkbox"
                           name="answers[{{ $question['key'] }}]"
                           value="{{ $question['title'] }}"
                           @if(isset($brif->{$question['key']}) && $brif->{$question['key']} === $question['title'])
                               checked
                           @endif>
                    <label for="answer_{{ $loop->index }}">{{ $question['title'] }}</label>
                </div>
            </div>
        @endforeach
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
                        <textarea name="answers[{{ $question['key'] }}]"
                                  placeholder="{{ $question['placeholder'] }}"
                                  class="form-control"
                                  maxlength="500">{{ $brif->{$question['key']} ?? '' }}</textarea>
                    @else
                        <input type="text"
                               name="answers[{{ $question['key'] }}]"
                               class="form-control"
                               value="{{ $brif->{$question['key']} ?? '' }}"
                               placeholder="{{ $question['placeholder'] }}"
                               maxlength="500">
                    @endif
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
                                    <textarea name="answers[{{ $question['key'] }}]"
                                              placeholder="{{ $question['placeholder'] }}"
                                              class="form-control"
                                              maxlength="500">{{ $brif->{$question['key']} ?? '' }}</textarea>
                                @else
                                    <input type="text"
                                           name="answers[{{ $question['key'] }}]"
                                           class="form-control"
                                           value="{{ $brif->{$question['key']} ?? '' }}"
                                           placeholder="{{ $question['placeholder'] }}"
                                           maxlength="500">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        {{-- Если это страница 15 — загрузка файлов --}}
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

<!-- Функции для навигации между шагами -->
<script>
   function goToPrev() {
    document.getElementById('actionInput').value = 'prev';
    document.getElementById('briefForm').submit();
}
function goToNext() {
    document.getElementById('actionInput').value = 'next';
    document.getElementById('briefForm').submit();
}

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
</script>
