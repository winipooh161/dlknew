@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="create-deal-form" action="{{ route('deals.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <!-- Добавляем скрытые поля для datestamps, которые заполняются скриптом -->
    <input type="hidden" name="start_date" id="start_date">
    <input type="hidden" name="project_duration" id="project_duration">
    <input type="hidden" name="project_end_date" id="project_end_date">

    <!-- БЛОК: Основная информация -->
    <fieldset class="module">
        <legend><h1>{{ $title_site }}</h1></legend>
       
        <div class="form-group-deal">
            <label for="price_service_option">Услуга по прайсу: <span class="required">*</span></label>
            <select id="price_service_option" name="price_service_option" class="form-control" required>
                <option value="">-- Выберите услугу --</option>
                <option value="экспресс планировка">Экспресс планировка</option>
                <option value="экспресс планировка с коллажами">Экспресс планировка с коллажами</option>
                <option value="экспресс проект с электрикой">Экспресс проект с электрикой</option>
                <option value="экспресс планировка с электрикой и коллажами">Экспресс планировка с электрикой и коллажами</option>
                <option value="экспресс проект с электрикой и визуализацией">Экспресс проект с электрикой и визуализацией</option>
                <option value="экспресс рабочий проект">Экспресс рабочий проект</option>
                <option value="экспресс эскизный проект с рабочей документацией">Экспресс эскизный проект с рабочей документацией</option>
                <option value="экспресс 3Dвизуализация">Экспресс 3Dвизуализация</option>
                <option value="экспресс полный дизайн-проект">Экспресс полный дизайн-проект</option>
                <option value="360 градусов">360 градусов</option>
            </select>
        </div>  
        <div class="form-group-deal">
            <label for="rooms_count_pricing">Количество комнат по прайсу:</label>
            <input type="number" id="rooms_count_pricing" name="rooms_count_pricing" class="form-control">
        </div>
        <div class="form-group-deal">
            <label for="execution_order_comment">Комментарий к Заказу для отдела исполнения:</label>
            <textarea id="execution_order_comment" name="execution_order_comment" class="form-control" rows="3" maxlength="1000"></textarea>
        </div>
        <div class="form-group-deal">
            <label for="execution_order_file">Прикрепить файл (PDF, JPG, PNG):</label>
            <input type="file" id="execution_order_file" name="execution_order_file" accept=".pdf,image/*">
        </div>
        <div class="form-group-deal">
            <label for="package">Пакет (1, 2 или 3): <span class="required">*</span></label>
            <input type="text" id="package" name="package" class="form-control" required>
        </div>
        <div class="form-group-deal">
            <label for="name">ФИО клиента: <span class="required">*</span></label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="form-group-deal">
            <label for="client_phone">Телефон: <span class="required">*</span></label>
            <input type="text" id="client_phone" name="client_phone"  class="form-control maskphone" required>
        </div>
        <div class="form-group-deal">
            <label for="client_timezone">Город/часовой пояс:</label>
            <select id="client_timezone" name="client_timezone" class="form-control">
                 <option value="">-- Выберите город --</option>
            </select>
        </div>
       
        <script>
            document.addEventListener("DOMContentLoaded", function(){
                // Автоматически устанавливаем сегодняшнюю дату в поле "Дата начала проекта"
                var today = new Date().toISOString().split("T")[0];
                document.getElementById("start_date").value = today;
                
                // При изменении срока проекта обновляем "Дата завершения проекта"
                document.getElementById("project_duration").addEventListener("input", function(){
                     var duration = parseInt(this.value, 10);
                     if (!isNaN(duration)) {
                         var startDate = new Date(document.getElementById("start_date").value);
                         startDate.setDate(startDate.getDate() + duration);
                         var endDate = startDate.toISOString().split("T")[0];
                         document.getElementById("project_end_date").value = endDate;
                     } else {
                         document.getElementById("project_end_date").value = "";
                     }
                });
            });
        </script>
        <!-- Убираем блок выбора партнёра, если пользователь partner -->
        @if(auth()->user()->status == 'partner')
            <div class="form-group-deal">
                <label>Партнер</label>
                <p>{{ auth()->user()->name }}</p>
                <input type="hidden" name="office_partner_id" value="{{ auth()->id() }}">
            </div>
        @else
            <!-- Если не partner, отображаем выбор партнеров -->
            <div class="form-group-deal">
                <label for="office_partner_id">Партнер:</label>
                <select id="office_partner_id" name="office_partner_id" class="form-control">
                    <option value="">-- Не выбрано --</option>
                    @foreach($partners as $partner)
                        <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Убираем блок выбора координатора, если пользователь coordinator -->
        @if(auth()->user()->status == 'coordinator')
            <div class="form-group-deal">
                <label>Отв. координатор</label>
                <p>{{ auth()->user()->name }}</p>
                <input type="hidden" name="coordinator_id" value="{{ auth()->id() }}">
            </div>
        @else
            <!-- Если не coordinator, отображаем выбор координаторов -->
            <div class="form-group-deal">
                <label for="coordinator_id">Отв. координатор:</label>
                <select id="coordinator_id" name="coordinator_id" class="form-control">
                    <option value="">-- Не выбрано (по умолчанию текущий пользователь) --</option>
                    @foreach($coordinators as $coord)
                        <option value="{{ $coord->id }}">{{ $coord->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="form-group-deal">
            <label for="total_sum">Общая сумма:</label>
            <input type="number" step="0.01" id="total_sum" name="total_sum" class="form-control">
        </div>
        <div class="form-group-deal">
            <label for="measuring_cost">Стоимость замеров:</label>
            <input type="number" step="0.01" id="measuring_cost" name="measuring_cost" class="form-control">
        </div>
        <div class="form-group-deal">
            <label for="client_info">Информация о клиенте:</label>
            <textarea id="client_info" name="client_info" class="form-control" rows="3"></textarea>
        </div>
        <div class="form-group-deal">
            <label for="payment_date">Дата оплаты:</label>
            <input type="date" id="payment_date" name="payment_date" class="form-control">
        </div>
        <div class="form-group-deal">
            <label for="execution_comment">Комментарий (исполнение):</label>
            <textarea id="execution_comment" name="execution_comment" class="form-control" rows="3" maxlength="1000"></textarea>
        </div>
        <div class="form-group-deal">
            <label for="comment">Общий комментарий:</label>
            <textarea id="comment" name="comment" class="form-control" rows="3" maxlength="1000"></textarea>
        </div>
       
    </fieldset>
    <button type="submit" class="btn btn-primary">Создать сделку</button>
</form>

<!-- Подключение необходимых библиотек (jQuery и Select2) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Укажите корректный путь к вашему JSON-файлу
    var jsonFilePath = '/cities.json';

    // Загружаем JSON-файл
    $.getJSON(jsonFilePath, function(data) {
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

        // Инициализируем Select2 с полученными данными
        $('#client_timezone').select2({
            data: select2Data,
            placeholder: "-- Выберите город --",
            allowClear: true
        });
    })
    .fail(function(jqxhr, textStatus, error) {
        console.error("Ошибка загрузки JSON файла: " + textStatus + ", " + error);
    });
});

// Маска для поля "№ проекта"
$("input.maskproject").on("input", function() {
    var value = this.value;
    if (!value.startsWith("Проект ")) {
        value = "Проект " + value.replace(/[^0-9]/g, "");
    } else {
        var digits = value.substring(7).replace(/[^0-9]/g, "");
        digits = digits.substring(0, 4);
        value = "Проект " + digits;
    }
    this.value = value;
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
// Маска для поля "Пакет": разрешаем только одну цифру
$("#package").on("input", function() {
    var val = this.value.replace(/\D/g, "");
    if(val.length > 1) { val = val.substring(0, 1); }
    this.value = val;
});
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var formChanged = false;
        var form = document.getElementById("create-deal-form");
    
        // Отслеживаем изменения в форме
        form.addEventListener("input", function () {
            formChanged = true;
        });
    
        // Предупреждение при попытке закрытия вкладки или перезагрузки страницы
        window.addEventListener("beforeunload", function (event) {
            if (formChanged) {
                event.preventDefault();
                event.returnValue = "Вы уверены, что хотите покинуть страницу? Все несохраненные данные будут потеряны.";
            }
        });
    
        // Убираем предупреждение при отправке формы (если пользователь сохраняет данные)
        form.addEventListener("submit", function () {
            formChanged = false;
        });
    });
</script>
