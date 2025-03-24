<!-- Скрипты и стили -->

<div class="brifs" id="brifs">
    <h1 class="flex">Ваши сделки</h1>
    <div class="filter">
        <form method="GET" action="{{ route('deal.cardinator') }}">
            <div class="search">
                <div class="search__input">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Поиск (имя, телефон, email, № проекта, примечание, город, сумма, даты)">
                    <img src="/storage/icon/search.svg" alt="Поиск">
                </div>
                <select name="status">
                    <option value="">Все статусы</option>
                    @foreach ($statuses as $option)
                        <option value="{{ $option }}" {{ $status === $option ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="variate__view">
                <button type="submit" name="view_type" value="blocks"
                    class="{{ $viewType === 'blocks' ? 'active-button' : '' }}">
                    <img src="/storage/icon/deal_card.svg" alt="Блоки">
                </button>
                <button type="submit" name="view_type" value="table"
                    class="{{ $viewType === 'table' ? 'active-button' : '' }}">
                    <img src="/storage/icon/deal__table.svg" alt="Таблица">
                </button>
            </div>
        </form>
    </div>
</div>

<div class="deal" id="deal">
    <div class="deal__body">
        <div class="deal__cardinator__lists">
            @if ($viewType === 'table')
                <table id="dealTable" border="1" class="deal-table">
                    <thead>
                        <tr>
                            <th>Имя клиента</th>
                            <th>Номер клиента</th>
                            <th>Сумма сделки</th>
                            <th>Статус</th>
                            <!-- Добавляем новый заголовок -->
                            <th>Партнер</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody class="flex_table__format_table">
                        @foreach ($deals as $dealItem)
                            <tr>
                                <td>{{ $dealItem->name }}</td>
                                <td>
                                    <a href="tel:{{ $dealItem->client_phone }}">
                                        {{ $dealItem->client_phone }}
                                    </a>
                                </td>
                                <td>{{ $dealItem->total_sum ?? 'Отсутствует' }}</td>
                                <td>{{ $dealItem->status }}</td>
                                <!-- Новая колонка с информацией Офис/Партнер -->
                                <td>
                                    @if($dealItem->office_partner_id)
                                        <a href="{{ route('profile.view', $dealItem->office_partner_id) }}">
                                            {{ \App\Models\User::find($dealItem->office_partner_id)->name ?? 'Не указан' }}
                                        </a>
                                    @else
                                        Не указан
                                    @endif
                                </td>
                                <td class="link__deistv">
                                    @if ($dealItem->registration_token)
                                        <a href="{{ $dealItem->registration_token ? route('register_by_deal', ['token' => $dealItem->registration_token]) : '' }}"
                                            onclick="event.preventDefault(); copyRegistrationLink(this.href)">
                                            <img src="/storage/icon/link.svg" alt="Регистрационная ссылка">
                                        </a>
                                    @else
                                        <a href="#">
                                            <img src="/storage/icon/link.svg" alt="Регистрационная ссылка">
                                        </a>
                                    @endif
                                    <!-- Изменяем ссылку на чат: используем ID чата сделки, полученный через связь $dealItem->chat -->
                                    <a href="{{ url('/chats?active_chat=' . ($dealItem->chat ? $dealItem->chat->id : '')) }}" onclick="window.location.href=this.href;">
                                        <img src="/storage/icon/chat.svg" alt="Чат">
                                    </a>
                                    <a href="{{ $dealItem->link ? url($dealItem->link) : '#' }}">
                                        <img src="/storage/icon/brif.svg" alt="Бриф">
                                    </a>
                                    @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
                                        <a href="{{ route('deal.change_logs.deal', ['deal' => $dealItem->id]) }}"
                                            class="btn btn-info btn-sm">
                                            <img src="/storage/icon/log.svg" alt="Логи">
                                        </a>
                                    @endif
                                    <!-- Кнопка редактирования с data-атрибутом -->
                                  
                                    @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                                    <button type="button" class="edit-deal-btn" data-id="{{ $dealItem->id }}">
                                        <img src="/storage/icon/add.svg" alt="Редактировать">
                                    </button>
                                @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <!-- Блочный вид -->
                <div class="faq__body__deal" id="all-deals-container">
                    <h4 class="flex">Все сделки</h4>
                    @if ($deals->isEmpty())
                        <div class="faq_block__deal faq_block-blur brifs__button__create-faq_block__deal" onclick="window.location.href='{{ route('deals.create') }}'">
                            @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                                <button >
                                    <img src="/storage/icon/add.svg" alt="Создать сделку">
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="faq_block__deal faq_block-blur brifs__button__create-faq_block__deal" onclick="window.location.href='{{ route('deals.create') }}'">
                            @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                                <button onclick="window.location.href='{{ route('deals.create') }}'">
                                    <img src="/storage/icon/add.svg" alt="Создать сделку">
                                </button>
                            @endif
                        </div>
                        @foreach ($deals as $dealItem)
                            <div class="faq_block__deal">
                                <div class="faq_item__deal">
                                    <div class="faq_question__deal flex between">
                                        <div class="faq_question__deal__info">

                                            @if ($dealItem->avatar_path)
                                                <div class="deal__avatar deal__avatar__cardinator">
                                                    <img src="{{ asset('storage/' . $dealItem->avatar_path) }}"
                                                        alt="Avatar">
                                                </div>
                                            @endif
                                            <div class="deal__cardinator__info">
                                                <div class="ctatus__deal___info">
                                                    <div class="div__status_info">{{ $dealItem->status }}</div>
                                                </div>
                                                <h4>{{ $dealItem->name }}</h4>
                                                <p class="doptitle">
                                                <h4>{{ $dealItem->project_number }}</h4>
                                                </p>
                                                <p>Телефон:
                                                    <a href="tel:{{ $dealItem->client_phone }}">
                                                        {{ $dealItem->client_phone }}
                                                    </a>
                                                </p>
                                                <!-- Добавляем информацию Офис/Партнер -->
                                                <p>Партнер:
                                                    @if($dealItem->office_partner_id)
                                                        <a href="{{ route('profile.view', $dealItem->office_partner_id) }}">
                                                            {{ \App\Models\User::find($dealItem->office_partner_id)->name ?? 'Не указан' }}
                                                        </a>
                                                    @else
                                                        Не указан
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <ul>
                                            <li>
                                                @php
                                                    // Убираем переменную $groupChat
                                                @endphp
                                                <a href="{{ url('/chats?active_chat=' . ($dealItem->chat ? $dealItem->chat->id : '')) }}" onclick="window.location.href=this.href;">
                                                    <img src="/storage/icon/chat.svg" alt="Чат">
                                                    <div class="icon">Чат</div>
                                                </a>
                                            </li>
                                            <li>
                                                @if (in_array(Auth::user()->status, ['coordinator', 'admin', 'partner']))
                                                    <button type="button" class="edit-deal-btn"
                                                        data-id="{{ $dealItem->id }}">
                                                        <img src="/storage/icon/create__blue.svg" alt="">
                                                        <span>Изменить</span>
                                                    </button>
                                                @endif

                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    <div class="pagination" id="all-deals-pagination"></div>
                </div>
            @endif
        </div>
    </div>
</div>
<div id="dealModalContainer"></div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<!-- Подключение библиотеки Axios -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/simplePagination.js/1.6/jquery.simplePagination.min.js"></script>
<script>
    $(function() {
        // Инициализация DataTable для табличного вида
        if ($('#dealTable').length) {
            $('#dealTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json'
                },
                paging: true,
                ordering: true,
                info: true,
                autoWidth: false,
                responsive: true
            });
        }

        // Пагинация для блочного вида
        function paginateContainer(container, paginationContainer, perPage = 6) {
            var $container = $(container);
            var $blocks = $container.find('.faq_block__deal');
            var total = $blocks.length;

            if (total <= perPage) {
                $blocks.show();
                return;
            }

            $blocks.hide();
            $blocks.slice(0, perPage).show();

            $(paginationContainer).pagination({
                items: total,
                itemsOnPage: perPage,
                cssStyle: 'light-theme',
                prevText: 'Предыдущая',
                nextText: 'Следующая',
                onPageClick: function(pageNumber, event) {
                    var start = (pageNumber - 1) * perPage;
                    var end = start + perPage;
                    $blocks.hide().slice(start, end).show();
                }
            });
        }

        // Вызов функции пагинации для блочного представления
        paginateContainer('#all-deals-container', '#all-deals-pagination', 6);

        var $editModal = $('#editModal'),
            $editForm = $('#editForm');

        // Функция инициализации Select2, вызывается после загрузки модального окна
        function initSelect2() {
            $('.select2-field').select2({
                width: '100%',
                placeholder: "Выберите значение",
                allowClear: true,
                dropdownParent: $('#editModal')
            });
        }

        var modalCache = {}; // Объект для кэширования модальных окон

        // Обработчик клика для открытия модального окна с данными сделки
        $('.edit-deal-btn').on('click', function() {
            var dealId = $(this).data('id');
            var $modalContainer = $("#dealModalContainer");

            // Проверяем, есть ли модальное окно в кэше
            if (modalCache[dealId]) {
                // Если есть, показываем его из кэша
                $modalContainer.html(modalCache[dealId]);
                initSelect2();
                $("#editModal").show().addClass('show');
                initModalFunctions();
            } else {
                // Если нет, загружаем с сервера
                // Показываем индикатор загрузки
                $modalContainer.html('<div class="loading">Загрузка...</div>');

                $.ajax({
                    url: "/deal/" + dealId + "/modal",
                    type: "GET",
                    success: function(response) {
                        // Сохраняем модальное окно в кэш
                        modalCache[dealId] = response.html;

                        // Вставляем HTML модального окна
                        $modalContainer.html(response.html);

                        // Инициализируем Select2 для dropdowns
                        initSelect2();

                        // Показываем модальное окно
                        $("#editModal").show().addClass('show');

                        // Обработчики закрытия модального окна
                        $('#closeModalBtn').on('click', function() {
                            $("#editModal").removeClass('show').hide();
                        });

                        $("#editModal").on('click', function(e) {
                            if (e.target === this) $(this).removeClass('show')
                            .hide();
                        });

                        // Инициализация других JS-функций для модального окна
                        initModalFunctions();
                    },
                    error: function(xhr, status, error) {
                        console.error("Ошибка загрузки данных сделки:", status, error);
                        alert(
                            "Ошибка загрузки данных сделки. Попробуйте обновить страницу.");
                    },
                    complete: function() {
                        // Скрываем индикатор загрузки
                        $('.loading').remove();
                    }
                });
            }

            // Динамическое изменение URL
            history.pushState(null, null, "#editDealModal");
        });

        // Обработчик закрытия модального окна
        $('#dealModalContainer').on('click', '#closeModalBtn', function() {
            $("#editModal").removeClass('show').hide();
            history.pushState("", document.title, window.location.pathname + window.location.search);
        });

        $('#dealModalContainer').on('click', '#editModal', function(e) {
            if (e.target === this) {
                $(this).removeClass('show').hide();
                history.pushState("", document.title, window.location.pathname + window.location
                .search);
            }
        });

        // Функция инициализации дополнительных JS-функций для модального окна
        function initModalFunctions() {
            var modules = $("#editModal fieldset.module__deal");
            var buttons = $("#editModal .button__points button");

            // Настройка переключения между вкладками
            modules.css({
                display: "none",
                opacity: "0",
                transition: "opacity 0.3s ease-in-out"
            });

            // Показываем активную вкладку (Лента)
            var activeModule = $("#module-zakaz");
            activeModule.css({
                display: "flex"
            });

            setTimeout(function() {
                activeModule.css({
                    opacity: "1"
                });
            }, 10);

            // Обработчик нажатия на кнопки вкладок
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

            // Обработчик отправки формы ленты
            $("#feed-form").on("submit", function(e) {
                e.preventDefault();
                var content = $("#feed-content").val().trim();
                if (!content) {
                    alert("Введите текст сообщения!");
                    return;
                }
                var dealId = $("#dealIdField").val();
                if (dealId) {
                    $.ajax({
                        url: "/deal/" + dealId + "/feed",
                        type: "POST",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            content: content
                        },
                        success: function(response) {
                            // ...existing code...
                        },
                        error: function(xhr) {
                            alert("Ошибка при добавлении записи: " + xhr.responseText);
                        }
                    });
                } else {
                    alert("Не удалось определить сделку. Пожалуйста, обновите страницу.");
                }
            });

            // Обработчик для файловых полей
            $('input[type="file"]').on('change', function() {
                var file = this.files[0];
                var fileName = file ? file.name : "";
                var fieldName = $(this).attr('id');
                var linkDiv = $('#' + fieldName + 'Link');

                if (fileName) {
                    linkDiv.html('<a href="' + URL.createObjectURL(file) + '" target="_blank">' +
                        fileName + '</a>');
                }
            });
        }

        $('#closeModalBtn').on('click', function() {
            $("#editModal").removeClass('show').hide();
        });
        $("#editModal").on('click', function(e) {
            if (e.target === this) $(this).removeClass('show').hide();
        });

      

        var modules = $("#editModal fieldset.module__deal");
        var buttons = $("#editModal .button__points button");
        modules.css({
            display: "none",
            opacity: "0",
            transition: "opacity 0.3s ease-in-out"
        });
        if (modules.length > 0) {
            $(modules[0]).css({
                display: "flex"
            });
            setTimeout(function() {
                $(modules[0]).css({
                    opacity: "1"
                });
            }, 10);
        }
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

        $.getJSON('/cities.json', function(data) {
            var grouped = {};
            $.each(data, function(i, item) {
                grouped[item.region] = grouped[item.region] || [];
                grouped[item.region].push({
                    id: item.city,
                    text: item.city
                });
            });
            var selectData = $.map(grouped, function(cities, region) {
                return {
                    text: region,
                    children: cities
                };
            });
            $('#client_timezone, #cityField').select2({
                data: selectData,
                placeholder: "-- Выберите город/часовой пояс --", // Изменён placeholder
                allowClear: true,
                minimumInputLength: 1, // Включён поиск по городам
                dropdownParent: $('#editModal')
            });
            // Инициализация select2 для поля "client_city" с обработкой поиска при вводе от 1 символа
            $('#client_city').select2({
                data: selectData,
                placeholder: "-- Выберите город/часовой пояс --",
                allowClear: true,
                minimumInputLength: 1,
                dropdownParent: $('#editModal')
            });
        }).fail(function(err) {
            console.error("Ошибка загрузки городов", err);
        });

        $('#responsiblesField').select2({
            placeholder: "Выберите ответственных",
            allowClear: true,
            dropdownParent: $('#editModal')
        });
        $('.select2-field').select2({
            width: '100%',
            placeholder: "Выберите значение",
            allowClear: true,
            dropdownParent: $('#editModal')
        });

        $("#feed-form").on("submit", function(e) {
            e.preventDefault();
            var content = $("#feed-content").val().trim();
            if (!content) {
                alert("Введите текст сообщения!");
                return;
            }
            var dealId = $("#dealIdField").val();
            if (dealId) {
                $.ajax({
                    url: "{{ url('/deal') }}/" + dealId + "/feed",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        content: content
                    },
                    success: function(response) {
                        $("#feed-content").val("");
                        var avatarUrl = response.avatar_url ? response.avatar_url :
                            "/storage/group_default.svg";
                        $("#feed-posts-container").prepend(`
                        <div class="feed-post">
                            <div class="feed-post-avatar">
                                <img src="${avatarUrl}" alt="${response.user_name}">
                            </div>
                            <div class="feed-post-text">
                                <div class="feed-author">${response.user_name}</div>
                                <div class="feed-content">${response.content}</div>
                                <div class="feed-date">${response.date}</div>
                            </div>
                        </div>
                    `);
                    },
                    error: function(xhr) {
                        alert("Ошибка при добавлении записи: " + xhr.responseText);
                    }
                });
            } else {
                alert("Не удалось определить сделку. Пожалуйста, обновите страницу.");
            }
        });

        $('input[type="file"]').on('change', function() {
            var file = this.files[0];
            var fileName = file ? file.name : "";
            var linkId = $(this).attr('id') + "FileName";
            if (fileName) {
                $('#' + linkId)
                    .text(fileName)
                    .attr('href', URL.createObjectURL(file))
                    .show();
            } else {
                $('#' + linkId).hide();
            }
        });
    });

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
<style>
    .select2-container {
        width: 100% !important;
    }

    .select2-selection--multiple {
        min-height: 38px !important;
    }

    .select2-selection__choice {
        padding: 2px 5px !important;
        margin: 2px !important;
        background-color: #e4e4e4 !important;
        border: none !important;
        border-radius: 3px !important;
    }
</style>
