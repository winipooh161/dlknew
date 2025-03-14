<div class="modal modal__deal" id="editModal" style="display: none;">
    <div class="modal-content">
        @if(isset($deal))
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
                    <a href="#" onclick="event.preventDefault(); copyRegistrationLink('{{ route('register_by_deal', ['token' => $deal->registration_token]) }}')">
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
        <fieldset class="module__deal" id="module-feed">
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
                <div class="form-group-deal">
                    <label>№ проекта:
                        @if(in_array($userRole, ['coordinator', 'admin']))
                            <input type="text" name="project_number" id="projectNumberField" value="{{ $deal->project_number }}" placeholder="Проект 6303,6304,6305" maxlength="21">
                        @else
                            <input type="text" name="project_number" id="projectNumberField" value="{{ $deal->project_number }}" disabled maxlength="21">
                        @endif
                    </label>
                </div>
                
                @if(in_array($userRole, ['coordinator', 'admin']))
                    <div class="form-group-deal">
                        <label>Аватар сделки:
                            <input type="file" name="avatar_path" id="dealAvatarField" accept="image/*">
                        </label>
                    </div>
                @elseif($deal->avatar_path)
                    <div class="form-group-deal">
                        <label>Аватар сделки:
                            <img src="{{ asset('storage/' . $deal->avatar_path) }}" alt="Аватар сделки">
                        </label>
                    </div>
                @endif
                
                @if(in_array($userRole, ['coordinator', 'admin', 'partner']))
                    <div class="form-group-deal">
                        <label>Пакет:
                            @if(in_array($userRole, ['partner', 'admin']))
                                <select name="package" id="packageField">
                                    <option value="">-- Выберите пакет --</option>
                                    <option value="1" {{ $deal->package == '1' ? 'selected' : '' }}>1</option>
                                    <option value="2" {{ $deal->package == '2' ? 'selected' : '' }}>2</option>
                                    <option value="3" {{ $deal->package == '3' ? 'selected' : '' }}>3</option>
                                </select>
                            @else
                                <input type="text" name="package" id="packageField" value="{{ $deal->package }}" disabled>
                            @endif
                        </label>
                    </div>
                @endif

                <div class="form-group-deal">
                    <label>Статус:
                        @if(in_array($userRole, ['coordinator', 'admin']))
                            <select name="status" id="statusField">
                                <option value="Ждем ТЗ" {{ $deal->status == 'Ждем ТЗ' ? 'selected' : '' }}>Ждем ТЗ</option>
                                <option value="Планировка" {{ $deal->status == 'Планировка' ? 'selected' : '' }}>Планировка</option>
                                <option value="Коллажи" {{ $deal->status == 'Коллажи' ? 'selected' : '' }}>Коллажи</option>
                                <option value="Визуализация" {{ $deal->status == 'Визуализация' ? 'selected' : '' }}>Визуализация</option>
                                <option value="Рабочка/сбор ИП" {{ $deal->status == 'Рабочка/сбор ИП' ? 'selected' : '' }}>Рабочка/сбор ИП</option>
                                <option value="Проект готов" {{ $deal->status == 'Проект готов' ? 'selected' : '' }}>Проект готов</option>
                                <option value="Проект завершен" {{ $deal->status == 'Проект завершен' ? 'selected' : '' }}>Проект завершен</option>
                                <option value="Проект на паузе" {{ $deal->status == 'Проект на паузе' ? 'selected' : '' }}>Проект на паузе</option>
                                <option value="Возврат" {{ $deal->status == 'Возврат' ? 'selected' : '' }}>Возврат</option>
                                <option value="Регистрация" {{ $deal->status == 'Регистрация' ? 'selected' : '' }}>Регистрация</option>
                                <option value="Бриф прикриплен" {{ $deal->status == 'Бриф прикриплен' ? 'selected' : '' }}>Бриф прикриплен</option>
                            </select>
                        @else
                            <input type="text" name="status" id="statusField" value="{{ $deal->status }}" readonly>
                        @endif
                    </label>
                </div>
                
                @if(in_array($userRole, ['coordinator', 'admin', 'partner']))
                    <div class="form-group-deal">
                        <label>Услуга по прайсу:
                            <select id="price_service_option" name="price_service_option" class="form-control" required>
                                <option value="">-- Выберите услугу --</option>
                                <option value="экспресс планировка" {{ $deal->price_service == 'экспресс планировка' ? 'selected' : '' }}>Экспресс планировка</option>
                                <option value="экспресс планировка с коллажами" {{ $deal->price_service == 'экспресс планировка с коллажами' ? 'selected' : '' }}>Экспресс планировка с коллажами</option>
                                <option value="экспресс проект с электрикой" {{ $deal->price_service == 'экспресс проект с электрикой' ? 'selected' : '' }}>Экспресс проект с электрикой</option>
                                <option value="экспресс планировка с электрикой и коллажами" {{ $deal->price_service == 'экспресс планировка с электрикой и коллажами' ? 'selected' : '' }}>Экспресс планировка с электрикой и коллажами</option>
                                <option value="экспресс проект с электрикой и визуализацией" {{ $deal->price_service == 'экспресс проект с электрикой и визуализацией' ? 'selected' : '' }}>Экспресс проект с электрикой и визуализацией</option>
                                <option value="экспресс рабочий проект" {{ $deal->price_service == 'экспресс рабочий проект' ? 'selected' : '' }}>Экспресс рабочий проект</option>
                                <option value="экспресс эскизный проект с рабочей документацией" {{ $deal->price_service == 'экспресс эскизный проект с рабочей документацией' ? 'selected' : '' }}>Экспресс эскизный проект с рабочей документацией</option>
                                <option value="экспресс 3Dвизуализация" {{ $deal->price_service == 'экспресс 3Dвизуализация' ? 'selected' : '' }}>Экспресс 3Dвизуализация</option>
                                <option value="экспресс полный дизайн-проект" {{ $deal->price_service == 'экспресс полный дизайн-проект' ? 'selected' : '' }}>Экспресс полный дизайн-проект</option>
                                <option value="360 градусов" {{ $deal->price_service == '360 градусов' ? 'selected' : '' }}>360 градусов</option>
                            </select>
                        </label>
                    </div>
                @endif
                
                @if(in_array($userRole, ['coordinator', 'admin']))
                    <div class="form-group-deal">
                        <label>Кол-во комнат по прайсу:
                            <input type="number" name="rooms_count_pricing" id="roomsCountField" value="{{ $deal->rooms_count_pricing }}">
                        </label>
                    </div>
                @endif
                
                @if(in_array($userRole, ['coordinator', 'admin']))
                    <div class="form-group-deal">
                        <label>Комментарий к заказу:
                            <textarea name="execution_order_comment" id="executionOrderCommentField" maxlength="1000">{{ $deal->execution_order_comment }}</textarea>
                        </label>
                    </div>
                @endif
                
                @if(isset($deal) && in_array($userRole, ['coordinator', 'admin']))
                    <div class="form-group-deal">
                        <label>Координатор:
                            @php $coordinators = \App\Models\User::where('status', 'coordinator')->get(); @endphp
                            <select name="coordinator_id" id="coordinatorField" class="select2-field">
                                <option value="">-- Не выбрано --</option>
                                @foreach ($coordinators as $coordinator)
                                    <option value="{{ $coordinator->id }}" {{ $deal->coordinator_id == $coordinator->id ? 'selected' : '' }}>{{ $coordinator->name }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                @elseif(isset($deal) && isset($deal->coordinator))
                    <div class="form-group-deal">
                        <label>Координатор:
                            <input type="text" value="{{ $deal->coordinator->name }}" disabled>
                        </label>
                    </div>
                @endif
                
                @if(isset($deal))
                    <div class="form-group-deal">
                        <label>ФИО клиента:
                            @if(in_array($userRole, ['coordinator', 'admin']))
                                <input type="text" name="name" id="nameField" value="{{ $deal->name }}" required>
                            @else
                                <input type="text" name="name" id="nameField" value="{{ $deal->name }}" disabled>
                            @endif
                        </label>
                    </div>
                @endif
                
                <div class="form-group-deal">
                    <label>Телефон:
                        @if(in_array($userRole, ['coordinator', 'admin']))
                            <input type="text" name="client_phone" id="phoneField" value="{{ $deal->client_phone }}" required>
                        @else
                            <input type="text" name="client_phone" id="phoneField" value="{{ $deal->client_phone }}" disabled>
                        @endif
                    </label>
                </div>
                
                @if(in_array($userRole, ['coordinator', 'admin']))
                    <div class="form-group-deal">
                        <label>Город/Часовой пояс:
                            <select name="client_city" id="cityField">
                                <!-- Здесь должны быть options городов -->
                            </select>
                        </label>
                    </div>
                @elseif($deal->client_city)
                    <div class="form-group-deal">
                        <label>Город/Часовой пояс:
                            <input type="text" value="{{ $deal->client_city }}" disabled>
                        </label>
                    </div>
                @endif
                
                <div class="form-group-deal">
                    <label>Email клиента:
                        @if(in_array($userRole, ['coordinator', 'admin']))
                            <input type="email" name="client_email" id="emailField" value="{{ $deal->client_email }}">
                        @else
                            <input type="email" name="client_email" id="emailField" value="{{ $deal->client_email }}" disabled>
                        @endif
                    </label>
                </div>
                
                @if(in_array($userRole, ['coordinator', 'admin']))
                    <div class="form-group-deal">
                        <label>Офис/Партнер:
                            @php $partners = \App\Models\User::where('status', 'partner')->get(); @endphp
                            <select name="office_partner_id" id="officePartnerField" class="select2-field">
                                <option value="">-- Не выбрано --</option>
                                @foreach ($partners as $partner)
                                    <option value="{{ $partner->id }}" {{ $deal->office_partner_id == $partner->id ? 'selected' : '' }}>{{ $partner->name }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                @elseif(isset($deal->office_partner))
                    <div class="form-group-deal">
                        <label>Офис/Партнер:
                            <input type="text" value="{{ $deal->office_partner->name }}" disabled>
                        </label>
                    </div>
                @endif

                @if(in_array($userRole, ['coordinator', 'admin']))
                    <div class="form-group-deal">
                        <label>Кто делает комплектацию:
                            <select name="completion_responsible" id="completionResponsibleField">
                                <option value="">-- Выберите вариант --</option>
                                <option value="клиент" {{ $deal->completion_responsible == 'клиент' ? 'selected' : '' }}>Клиент</option>
                                <option value="партнер" {{ $deal->completion_responsible == 'партнер' ? 'selected' : '' }}>Партнер</option>
                                <option value="шопинг-лист" {{ $deal->completion_responsible == 'шопинг-лист' ? 'selected' : '' }}>Шопинг-лист</option>
                                <option value="закупки и снабжение от УК" {{ $deal->completion_responsible == 'закупки и снабжение от УК' ? 'selected' : '' }}>Нужны закупки и снабжение от УК</option>
                            </select>
                        </label>
                    </div>
                @elseif($deal->completion_responsible)
                    <div class="form-group-deal">
                        <label>Кто делает комплектацию:
                            <input type="text" value="{{ $deal->completion_responsible }}" disabled>
                        </label>
                    </div>
                @endif

                <!-- Показываем остальные поля только администраторам и координаторам -->
                @if(in_array($userRole, ['coordinator', 'admin']))
                    <div class="form-group-deal">
                        <label>№ договора:
                            <input type="text" name="contract_number" id="contractNumberField" value="{{ $deal->contract_number }}">
                        </label>
                    </div>
                    
                    <div class="form-group-deal">
                        <label>Дата создания сделки:
                            <input type="date" name="created_date" id="createdDateField" value="{{ $deal->created_date }}">
                        </label>
                    </div>
                    
                    <div class="form-group-deal">
                        <label>Дата оплаты:
                            <input type="date" name="payment_date" id="paymentDateField" value="{{ $deal->payment_date }}">
                        </label>
                    </div>
                    
                    <div class="form-group-deal">
                        <label>Сумма заказа:
                            <input type="number" name="total_sum" id="totalSumField" value="{{ $deal->total_sum }}" step="0.01">
                        </label>
                    </div>
                    
                    <div class="form-group-deal">
                        <label>Приложение:
                            <input type="file" name="contract_attachment" id="contractAttachmentField" accept="application/pdf,image/jpeg,image/jpg">
                        </label>
                        @if($deal->contract_attachment)
                            <div class="file-link">
                                <a href="{{ asset('storage/' . $deal->contract_attachment) }}" target="_blank">Просмотр файла</a>
                            </div>
                        @endif
                    </div>
                @endif
                
                <div class="form-group-deal">
                    <label>Примечание:
                        @if(in_array($userRole, ['coordinator', 'admin']))
                            <textarea name="deal_note" id="dealNoteField">{{ $deal->deal_note }}</textarea>
                        @else
                            <textarea name="deal_note" id="dealNoteField" disabled>{{ $deal->deal_note }}</textarea>
                        @endif
                    </label>
                </div>
                
                @if(in_array($userRole, ['coordinator', 'admin']))
                    <div class="form-group-deal">
                        <label>Замеры:
                            <input type="file" name="measurements_file" id="measurementsFileField" accept=".pdf,.dwg,image/*">
                            @if($deal->measurements_file)
                                <div class="file-link">
                                    <a href="{{ asset('storage/' . $deal->measurements_file) }}" target="_blank">Просмотр файла</a>
                                </div>
                            @endif
                            <input type="text" name="measurements_text" id="measurementsTextField" value="{{ $deal->measurements_text }}" placeholder="Краткое описание (до 200 символов)" maxlength="200">
                        </label>
                    </div>
                    
                    <div class="form-group-deal">
                        <label>Комментарии по замерам:
                            <textarea name="measurements_comment" id="measurementsCommentField">{{ $deal->measurements_comment }}</textarea>
                        </label>
                    </div>
                @endif
            </fieldset>

            <!-- Модуль: Работа над проектом -->
            <fieldset class="module__deal" id="module-rabota">
                <legend>Работа над проектом</legend>
                
                @if(in_array($userRole, ['coordinator', 'admin', 'partner']))
                    <div class="form-group-deal">
                        <label>Дата старта работы по проекту:
                            <input type="date" name="start_date" id="startDateField" value="{{ $deal->start_date }}">
                        </label>
                    </div>
                    
                    <div class="form-group-deal">
                        <label>Общий срок проекта (в рабочих днях):
                            <input type="number" name="project_duration" id="projectDurationField" value="{{ $deal->project_duration }}">
                        </label>
                    </div>
                    
                    <div class="form-group-deal">
                        <label>Дата завершения:
                            <input type="date" name="project_end_date" id="projectEndDateField" value="{{ $deal->project_end_date }}">
                        </label>
                    </div>
                    
                    <!-- Только для координаторов и админов доступен выбор исполнителей -->
                    @if(in_array($userRole, ['coordinator', 'admin']))
                        <div class="form-group-deal">
                            <label>Архитектор:
                                @php $architects = \App\Models\User::where('status', 'architect')->get(); @endphp
                                <select name="architect_id" id="architectField">
                                    <option value="">-- Не выбрано --</option>
                                    @foreach ($architects as $architect)
                                        <option value="{{ $architect->id }}" {{ $deal->architect_id == $architect->id ? 'selected' : '' }}>{{ $architect->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    @elseif(isset($deal->architect))
                        <div class="form-group-deal">
                            <label>Архитектор:
                                <input type="text" value="{{ $deal->architect->name }}" disabled>
                            </label>
                        </div>
                    @endif
                    
                    <!-- Только координатор и админ могут загружать файлы -->
                    @if(in_array($userRole, ['coordinator', 'admin']))
                        <div class="form-group-deal">
                            <label>Планировка финал (PDF, до 20мб):
                                <input type="file" name="plan_final" id="planFinalField" accept="application/pdf">
                                @if($deal->plan_final)
                                    <div class="file-link">
                                        <a href="{{ asset('storage/' . $deal->plan_final) }}" target="_blank">Просмотр файла</a>
                                    </div>
                                @endif
                            </label>
                        </div>
                        
                        <div class="form-group-deal">
                            <label>Дизайнер:
                                @php $designers = \App\Models\User::where('status', 'designer')->get(); @endphp
                                <select name="designer_id" id="designerField">
                                    <option value="">-- Не выбрано --</option>
                                    @foreach ($designers as $designer)
                                        <option value="{{ $designer->id }}" {{ $deal->designer_id == $designer->id ? 'selected' : '' }}>{{ $designer->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                        
                        <div class="form-group-deal">
                            <label>Коллаж финал (PDF, до 200мб):
                                <input type="file" name="final_collage" id="finalCollageField" accept="application/pdf">
                                @if($deal->final_collage)
                                    <div class="file-link">
                                        <a href="{{ asset('storage/' . $deal->final_collage) }}" target="_blank">Просмотр файла</a>
                                    </div>
                                @endif
                            </label>
                        </div>
                        
                        <div class="form-group-deal">
                            <label>Визуализатор:
                                @php $visualizers = \App\Models\User::where('status', 'visualizer')->get(); @endphp
                                <select name="visualizer_id" id="visualizerField">
                                    <option value="">-- Не выбрано --</option>
                                    @foreach ($visualizers as $visualizer)
                                        <option value="{{ $visualizer->id }}" {{ $deal->visualizer_id == $visualizer->id ? 'selected' : '' }}>{{ $visualizer->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    @else
                        <!-- Для партнеров только просмотр выбранных специалистов и файлов -->
                        @if(isset($deal->architect))
                            <div class="form-group-deal">
                                <label>Архитектор:
                                    <input type="text" value="{{ $deal->architect->name }}" disabled>
                                </label>
                            </div>
                        @endif
                        
                        @if($deal->plan_final)
                            <div class="form-group-deal">
                                <label>Планировка финал:
                                    <a href="{{ asset('storage/' . $deal->plan_final) }}" target="_blank">Просмотр файла</a>
                                </label>
                            </div>
                        @endif
                        
                        @if(isset($deal->designer))
                            <div class="form-group-deal">
                                <label>Дизайнер:
                                    <input type="text" value="{{ $deal->designer->name }}" disabled>
                                </label>
                            </div>
                        @endif
                        
                        @if($deal->final_collage)
                            <div class="form-group-deal">
                                <label>Коллаж финал:
                                    <a href="{{ asset('storage/' . $deal->final_collage) }}" target="_blank">Просмотр файла</a>
                                </label>
                            </div>
                        @endif
                        
                        @if(isset($deal->visualizer))
                            <div class="form-group-deal">
                                <label>Визуализатор:
                                    <input type="text" value="{{ $deal->visualizer->name }}" disabled>
                                </label>
                            </div>
                        @endif
                    @endif
                    
                    <!-- Ссылку на визуализацию могут добавлять все исполнители -->
                    <div class="form-group-deal">
                        <label>Ссылка на визуализацию:
                            <input type="url" name="visualization_link" id="visualizationLinkField" value="{{ $deal->visualization_link }}" placeholder="Вставьте ссылку">
                        </label>
                    </div>
                    
                    <!-- Финальный файл проекта может загружать только координатор или администратор -->
                    @if(in_array($userRole, ['coordinator', 'admin']))
                        <div class="form-group-deal">
                            <label>Финал проекта (PDF, до 200мб):
                                <input type="file" name="final_project_file" id="finalProjectFileField" accept="application/pdf">
                                @if($deal->final_project_file)
                                    <div class="file-link">
                                        <a href="{{ asset('storage/' . $deal->final_project_file) }}" target="_blank">Просмотр файла</a>
                                    </div>
                                @endif
                            </label>
                        </div>
                    @elseif($deal->final_project_file)
                        <div class="form-group-deal">
                            <label>Финал проекта:
                                <a href="{{ asset('storage/' . $deal->final_project_file) }}" target="_blank">Просмотр файла</a>
                            </label>
                        </div>
                    @endif
                @else
                    <!-- Для других пользователей только информация для просмотра -->
                    <div class="form-group-deal">
                        <label>Дата старта работы: <span>{{ $deal->start_date }}</span></label>
                    </div>
                    <div class="form-group-deal">
                        <label>Срок проекта: <span>{{ $deal->project_duration }} дней</span></label>
                    </div>
                    <div class="form-group-deal">
                        <label>Дата завершения: <span>{{ $deal->project_end_date }}</span></label>
                    </div>
                @endif
            </fieldset>

            <!-- Модуль Финал проекта только для координаторов и администраторов -->
            @if (in_array(Auth::user()->status, ['coordinator', 'admin']))
                <!-- Модуль: Финал проекта -->
                <fieldset class="module__deal" id="module-final">
                    <legend>Финал проекта</legend>
                    <div class="form-group-deal">
                        <label>Акт выполненных работ (PDF):
                            <input type="file" name="work_act" id="workActField" accept="application/pdf">
                        </label>
                        <div id="workActFieldLink" class="file-link">
                            @if($deal->work_act)
                                <a href="{{ asset('storage/' . $deal->work_act) }}" target="_blank">Просмотр файла</a>
                            @endif
                        </div>
                    </div>
                    <div class="form-group-deal">
                        <label>Оценка за проект (от клиента):
                            <input type="number" name="client_project_rating" id="clientProjectRatingField" value="{{ $deal->client_project_rating }}" min="0" max="10" step="0.5">
                        </label>
                    </div>
                    <div class="form-group-deal">
                        <label>Оценка архитектора (Клиент):
                            <input type="number" name="architect_rating_client" id="architectRatingClientField" value="{{ $deal->architect_rating_client }}" min="0" max="10" step="0.5">
                        </label>
                    </div>
                    <div class="form-group-deal">
                        <label>Оценка архитектора (Партнер):
                            <input type="number" name="architect_rating_partner" id="architectRatingPartnerField" value="{{ $deal->architect_rating_partner }}" min="0" max="10" step="0.5">
                        </label>
                    </div>
                    <div class="form-group-deal">
                        <label>Оценка архитектора (Координатор):
                            <input type="number" name="architect_rating_coordinator" id="architectRatingCoordinatorField" value="{{ $deal->architect_rating_coordinator }}" min="0" max="10" step="0.5">
                        </label>
                    </div>
                    <div class="form-group-deal">
                        <label>Скрин чата с оценкой и актом (JPEG):
                            <input type="file" name="chat_screenshot" id="chatScreenshotField" accept="image/jpeg,image/jpg,image/png">
                        </label>
                        <a id="chatScreenshotFileName" href="{{ asset('storage/' . $deal->chat_screenshot) }}" style="display:{{ $deal->chat_screenshot ? 'block' : 'none' }};">Просмотр файла</a>
                    </div>
                    <div class="form-group-deal">
                        <label>Комментарий координатора:
                            <textarea name="coordinator_comment" id="coordinatorCommentField" maxlength="1000">{{ $deal->coordinator_comment }}</textarea>
                        </label>
                    </div>
                    <div class="form-group-deal">
                        <label>Исходный файл архикад (pln, dwg):
                            <input type="file" name="archicad_file" id="archicadFileField" accept=".pln,.dwg">
                        </label>
                        <a id="archicadFileName" href="{{ asset('storage/' . $deal->archicad_file) }}" style="display:{{ $deal->archicad_file ? 'block' : 'none' }};">Просмотр файла</a>
                    </div>
                    <div class="form-group-deal">
                        <label>№ договора:
                            <input type="text" name="contract_number" id="contractNumberField" value="{{ $deal->contract_number }}">
                        </label>
                    </div>
                    <div class="form-group-deal">
                        <label>Дата создания сделки:
                            <input type="date" name="created_date" id="createdDateField" value="{{ $deal->created_date }}">
                        </label>
                    </div>
                    <div class="form-group-deal">
                        <label>Дата оплаты:
                            <input type="date" name="payment_date" id="paymentDateField" value="{{ $deal->payment_date }}">
                        </label>
                    </div>
                    <div class="form-group-deal">
                        <label>Сумма Заказа:
                            <input type="number" name="total_sum" id="totalSumField" value="{{ $deal->total_sum }}" step="0.01">
                        </label>
                    </div>
                    <div class="form-group-deal">
                        <label>Приложение договора:
                            <input type="file" name="contract_attachment" id="contractAttachmentField" accept="application/pdf,image/jpeg,image/jpg,image/png">
                        </label>
                        <div id="contractAttachmentFieldLink" class="file-link">
                            @if($deal->contract_attachment)
                                <a href="{{ asset('storage/' . $deal->contract_attachment) }}" target="_blank">Просмотр файла</a>
                            @endif
                        </div>
                    </div>
                    <div class="form-group-deal">
                        <label>Примечание:
                            <textarea name="deal_note" id="dealNoteField">{{ $deal->deal_note }}</textarea>
                        </label>
                    </div>
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