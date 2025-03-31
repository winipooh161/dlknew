<!-- Область чата -->
<div class="col-md-9 col-lg-9 p-0 chat-area">
    <!-- Заголовок чата -->
    <div class="chat-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <h5 id="current-chat-name">Выберите чат</h5>
            <div id="chat-status" class="small text-muted ms-2"></div>
        </div>
       
    </div>
    <!-- Сообщения -->
    <div class="messages-box position-relative" id="messages-container">
        <div class="empty-chat">
            <i class="bi bi-chat-dots"></i>
            <p>Выберите контакт, чтобы начать общение</p>
        </div>
        <div class="loading-overlay" id="messages-loading" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Загрузка сообщений...</span>
            </div>
        </div>
    </div>
    
    <!-- Индикатор набора текста -->
    <div class="typing-indicator" id="typing-indicator">
        <i class="bi bi-pencil-fill me-2"></i> Собеседник печатает...
    </div>
    
    <!-- Форма отправки сообщений -->
    <div class="chat-input-area">
        <!-- Область предпросмотра файлов -->
        <div id="file-preview" class="file-preview">
            <div id="file-preview-list">
                <!-- Элементы предпросмотра будут добавляться здесь -->
            </div>
        </div>
        <!-- Прогресс загрузки файлов -->
        <div class="file-upload-progress">
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                     role="progressbar" aria-valuenow="0" aria-valuemin="0" 
                     aria-valuemax="100" style="width: 0%"></div>
            </div>
        </div>
        
        <!-- Сообщение об ошибке -->
        <div id="error-message" class="error-message" style="display: none;"></div>
        
        <!-- Форма сообщения -->
        <form id="message-form" class="d-none">
            <div class="input-group">
                <input type="text" class="form-control" id="message-input" placeholder="Введите сообщение...">
                <button type="button" class="btn btn-outline-secondary" id="emoji-button" title="Эмодзи">
                    <i class="bi bi-emoji-smile"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary" id="attachment-button" title="Прикрепить файл">
                    <i class="bi bi-paperclip"></i>
                </button>
                <input type="file" id="file-input" class="d-none" multiple>
                <button type="submit" class="btn btn-primary" id="send-button" title="Отправить">
                    <i class="bi bi-send"></i>
                </button>
                <div class="loading-spinner" id="loading-spinner">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Отправка...</span>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Панель эмодзи -->
    @include('chats.components.emoji-picker')
</div>

<style>
.new-messages-notifier {
    position: absolute;
    bottom: 70px;
    left: 50%;
    transform: translateX(-50%);
    background-color: #0084ff;
    color: white;
    padding: 8px 15px;
    border-radius: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    cursor: pointer;
    display: none;
    align-items: center;
    gap: 8px;
    z-index: 5;
    animation: bounce 1s infinite alternate;
}

.new-messages-notifier i {
    font-size: 1.2em;
}

@keyframes bounce {
    0% { transform: translateX(-50%) translateY(0); }
    100% { transform: translateX(-50%) translateY(-5px); }
}
</style>
