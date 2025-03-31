<!-- Левая панель с контактами -->
<div class="col-md-3 col-lg-3 contacts-list" id="contacts-list">
    <!-- Верхняя часть с поиском -->
    <div class=" border-bottom">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Сообщения</h4>
            <div>
                <!-- Удаляем кнопку обновления сообщений, так как всё обновляется автоматически -->
                <div id="group-actions" style="display: none;">
                    <button id="create-group-btn" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#create-group-modal">
                        <i class="bi bi-people-fill me-1"></i> Создать группу
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Переключение между личными и групповыми чатами -->
        @include('chats.components.tab-selector')
        
        <!-- Поиск -->
        <div class="input-group mb-3">
            <span class="input-group-text bg-light border-0">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" id="search-contact" class="form-control border-0 bg-light" placeholder="Поиск...">
        </div>
    </div>
    
    <!-- Индикатор загрузки контактов -->
    <div id="contacts-loading" class="d-flex justify-content-center align-items-center py-4" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Загрузка...</span>
        </div>
    </div>
    
    <!-- Список контактов -->
    <div class="contacts-body">
        <ul id="contacts" class="list-unstyled mb-0">
            <!-- Контакты будут добавлены динамически через JavaScript -->
        </ul>
    </div>
</div>

<!-- Кнопка показа контактов на мобильных -->
<button class="btn btn-sm btn-outline-secondary show-contacts-btn" id="show-contacts-btn">
    <i class="bi bi-list"></i>
</button>

<!-- Модальное окно создания группы -->
<div class="modal fade create-group-modal" id="create-group-modal" tabindex="-1" aria-labelledby="createGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createGroupModalLabel">Создание групповой беседы</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div id="avatar-preview" class="group-avatar-preview mb-3">
                            <img id="preview-img" src="{{ asset('storage/icon/profile.svg') }}" alt="Аватар группы" class="img-fluid rounded">
                            <div class="change-avatar">
                                <i class="bi bi-camera-fill"></i>
                                <span>Изменить</span>
                            </div>
                        </div>
                        <input type="file" id="group-avatar" class="d-none" accept="image/*">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="group-name" placeholder="Название группы">
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" id="group-description" rows="3" placeholder="Описание группы (необязательно)"></textarea>
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-primary" id="selected-count">0</span> участников выбрано
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="search-members" class="form-control border-0 bg-light" placeholder="Поиск участников...">
                            </div>
                        </div>
                        <div id="group-members-list" class="group-members-list">
                            <!-- Список пользователей для добавления в группу -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="save-group-btn">Создать группу</button>
            </div>
        </div>
    </div>
</div>
