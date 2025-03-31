document.addEventListener('DOMContentLoaded', function() {
    // Проверка наличия элементов перед их использованием
    if (!document.getElementById('contacts') || 
        !document.getElementById('messages-container') || 
        !document.getElementById('message-form')) {
        console.warn('Необходимые DOM-элементы для чата не найдены');
        return; // Завершаем выполнение скрипта
    }
    
    // Элементы управления
    const contactsContainer = document.getElementById('contacts');
    const messagesContainer = document.getElementById('messages-container');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const emojiButton = document.getElementById('emoji-button');
    const emojiPicker = document.getElementById('emoji-picker');
    const attachmentButton = document.getElementById('attachment-button');
    const fileInput = document.getElementById('file-input');
    const filePreview = document.getElementById('file-preview');
    const filePreviewList = document.getElementById('file-preview-list');
    // Удаляем ссылку на кнопку обновления сообщений, так как она больше не нужна
    const currentChatName = document.getElementById('current-chat-name');
    const chatStatus = document.getElementById('chat-status');
    const searchInput = document.getElementById('search-contact');
    const contactsLoading = document.getElementById('contacts-loading');
    const messagesLoading = document.getElementById('messages-loading');
    const fileUploadProgress = document.querySelector('.file-upload-progress');
    const progressBar = document.querySelector('.progress-bar');
    const typingIndicator = document.getElementById('typing-indicator');
    const chatNotification = document.getElementById('chat-notification');
    const notificationText = document.getElementById('notification-text');
    const sendButton = document.getElementById('send-button');
    const errorMessageEl = document.getElementById('error-message');
    const showContactsBtn = document.getElementById('show-contacts-btn');
    const contactsList = document.getElementById('contacts-list');
    const loadingSpinner = document.getElementById('loading-spinner'); // Добавляем эту строку
    
    // Данные
    let currentChatId = null;
    let lastMessageId = 0;
    let contacts = [];
    let messageRefreshInterval = null;
    let typingTimeout = null;
    let selectedFiles = [];
    let currentTab = 'private'; // По умолчанию личные чаты
    let isGroup = false; // Флаг для определения типа текущего чата (личный/групповой)
    let globalCheckInterval = null; // Интервал для проверки новых сообщений во всех чатах
    let autoRefreshInterval = null; // Интервал для автоматического обновления списка контактов
    let notifiedMessageIds = new Set(); // Добавляем Set для хранения ID сообщений, о которых уже уведомляли

    // Показать сообщение об ошибке
    function showError(message) {
        if (errorMessageEl) {
            errorMessageEl.innerHTML = message;
            errorMessageEl.style.display = 'block';
            
            // Автоматически скрыть через 5 секунд
            setTimeout(() => {
                hideError();
            }, 5000);
        }
    }

    // Скрыть сообщение об ошибке
    function hideError() {
        if (errorMessageEl) {
            errorMessageEl.style.display = 'none';
        }
    }

    // Загрузка списка контактов
    function loadContacts() {
        if (!contactsContainer) {
            console.error('Элемент контейнера контактов не найден');
            return;
        }

        try {
            // Показываем индикатор загрузки только если элемент существует
            if (contactsLoading) {
                contactsLoading.style.cssText = 'display: none !important';
                // Небольшая пауза перед показом индикатора
                setTimeout(() => {
                    contactsLoading.style.cssText = 'display: flex !important';
                }, 50);
            }
            
            hideError();
            
            // Очищаем список контактов перед загрузкой новых
            contacts = [];
            contactsContainer.innerHTML = '';
            
            // Выбираем URL API в зависимости от выбранной вкладки
            let apiUrl = currentTab === 'private'
                ? '/api/contacts'
                : '/api/chat-groups';
            
            axios.get(apiUrl)
                .then(response => {
                    // Сохраняем данные с пометкой типа чата
                    contacts = response.data.map(item => ({
                        ...item,
                        chatType: currentTab // Добавляем пометку о типе чата (private или group)
                    }));
                    renderContacts(contacts);
                    
                    // Скрываем индикатор загрузки
                    if (contactsLoading) {
                        contactsLoading.style.cssText = 'display: none !important';
                    }
                })
                .catch(error => {
                    console.error('Ошибка при загрузке контактов:', error);
                    
                    // Обязательно скрываем индикатор загрузки даже при ошибке
                    if (contactsLoading) {
                        contactsLoading.style.cssText = 'display: none !important';
                    }
                    
                    // Показываем ошибку пользователю
                    let errorMessage = 'Не удалось загрузить список контактов';
                    if (error.response && error.response.data && error.response.data.error) {
                        errorMessage = error.response.data.error;
                    }
                    showError(errorMessage);
                    
                    // Временные данные для демонстрации в случае ошибки
                    contacts = [];
                    
                    // Если система в разработке, добавляем демо-данные
                    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1' || window.location.hostname === 'lk') {
                        contacts = [
                            { id: 1, name: 'Иван Петров', avatar: '/storage/icon/profile.svg', status: 'online', lastMessage: 'Привет, как дела?', unreadCount: 2, lastActivity: 'Сегодня, 14:30' },
                            { id: 2, name: 'Анна Сидорова', avatar: '/storage/icon/profile.svg', status: 'offline', lastMessage: 'Увидимся завтра', unreadCount: 0, lastActivity: 'Вчера, 19:45' },
                            { id: 3, name: 'Алексей Иванов', avatar: '/storage/icon/profile.svg', status: 'online', lastMessage: 'Документы готовы', unreadCount: 5, lastActivity: 'Сегодня, 12:10' }
                        ];
                    }
                    
                    renderContacts(contacts);
                })
                .finally(() => {
                    // Дополнительная проверка, чтобы индикатор загрузки точно был скрыт
                    if (contactsLoading) {
                        contactsLoading.style.cssText = 'display: none !important';
                    }
                });
        } catch (err) {
            console.error('Критическая ошибка в функции loadContacts:', err);
            // В случае критической ошибки обязательно скрываем индикатор загрузки
            if (contactsLoading) {
                contactsLoading.style.cssText = 'display: none !important';
            }
        }
    }

    // Отрисовка списка контактов
    function renderContacts(contacts) {
        contactsContainer.innerHTML = '';
        
        if (contacts.length === 0) {
            contactsContainer.innerHTML = '<div class="p-3 text-center text-muted">Нет доступных контактов</div>';
            return;
        }
        
        // Фильтруем контакты по текущему типу чата для гарантии
        const filteredContacts = contacts.filter(item => 
            !item.chatType || item.chatType === currentTab
        );
        
        filteredContacts.forEach(item => {
            // Проверка наличия аватарки, если нет - ставим дефолтную
            const avatarUrl = item.avatar || '/storage/icon/profile.svg';
            
            // Если отображаем личные чаты, работаем с данными контакта,
            // а если групповые — показываем название группы и описание
            let displayName = item.name;
            let extraInfo = item.lastMessage || 'Нет сообщений';
            if (currentTab === 'group') {
                displayName = item.name; // имя группы
                extraInfo = item.description || '';
            }
            
            // Определяем статус пользователя и соответствующий текст/иконку
            const isOnline = item.status === 'online';
            const statusIndicator = isOnline 
                ? '<span class="online-indicator"></span>' 
                : '';
            const statusText = isOnline 
                ? 'В сети' 
                : `Был(а) ${item.lastActivity}`;
            
            // Добавляем класс new-message, если есть непрочитанные сообщения
            const hasNewMessages = item.unreadCount && item.unreadCount > 0;
            
            const contactEl = document.createElement('div');
            contactEl.className = `contact-item d-flex align-items-center ${currentChatId === item.id ? 'active' : ''} ${hasNewMessages ? 'new-message' : ''}`;
            contactEl.dataset.id = item.id;
            
            contactEl.innerHTML = `
                <img src="${avatarUrl}" alt="${displayName}" class="contact-avatar me-3">
                <div class="contact-info">
                    <div class="d-flex justify-content-between">
                        <div class="contact-name">${displayName}</div>
                        ${hasNewMessages ? `<span class="unread-badge">${item.unreadCount}</span>` : ''}
                    </div>
                    <div class="small text-truncate">${extraInfo}</div>
                    <div class="small text-muted">
                        ${statusIndicator}
                        ${statusText}
                    </div>
                </div>
            `;
            
            contactEl.addEventListener('click', () => {
                // Удаление активного класса у всех контактов
                document.querySelectorAll('.contact-item').forEach(item => 
                    item.classList.remove('active'));
                // Добавление активного класса выбранному контакту
                contactEl.classList.add('active');
                
                // Удаляем класс new-message
                contactEl.classList.remove('new-message');
                
                // Скрываем контакты на мобильных после выбора
                if (window.innerWidth < 768) {
                    contactsList.classList.remove('active');
                }
                
                // Обновление счетчика непрочитанных сообщений
                const unreadBadge = contactEl.querySelector('.unread-badge');
                if (unreadBadge) {
                    unreadBadge.remove();
                }
                
                // Если был активен другой интервал обновления, очищаем его
                if (messageRefreshInterval) {
                    clearInterval(messageRefreshInterval);
                }
                
                // Установка текущего чата
                currentChatId = parseInt(item.id);
                currentChatName.textContent = displayName;
                chatStatus.innerHTML = `${item.status === 'online' ? 
                    '<span class="online-indicator"></span>В сети' : 'Не в сети'}`;
                
                // Явно очищаем контейнер сообщений перед загрузкой новых
                if (messagesContainer) {
                    messagesContainer.innerHTML = '';
                }
                
                // Отображение формы отправки
                messageForm.classList.remove('d-none');
                
                // Сбрасываем последний ID сообщения
                lastMessageId = 0;
                
                // Загрузка сообщений
                loadMessages(item.id);
                
                // Обновляем счетчик непрочитанных сообщений в меню
                if (typeof window.checkUnreadMessages === 'function') {
                    window.checkUnreadMessages();
                }
                
                // Настройка интервала обновления сообщений на 1 секунду
                messageRefreshInterval = setInterval(() => {
                    if (currentChatId) {
                        loadNewMessages(currentChatId);
                    }
                }, 1000); // Обновление каждую секунду
            });
            
            contactsContainer.appendChild(contactEl);
        });
    }

    // Загрузка сообщений чата
    function loadMessages(chatId) {
        // Дополнительная гарантия полной очистки сообщений
        if (messagesContainer) {
            messagesContainer.innerHTML = '';
        }
        
        if (messagesLoading) {
            messagesLoading.style.display = 'flex';
        }
        
        hideError();
        
        // Очищаем маркер непрочитанных сообщений
        clearUnreadMarker(chatId);
        
        // Определяем URL API в зависимости от типа чата
        const apiUrl = currentTab === 'group' 
            ? `/api/chat-groups/${chatId}/messages` 
            : `/api/chats/${chatId}/messages`;
        
        axios.get(apiUrl)
            .then(response => {
                const data = response.data;
                isGroup = currentTab === 'group'; // Запоминаем тип текущего чата
                
                // Еще раз очищаем контейнер перед рендерингом сообщений
                if (messagesContainer) {
                    messagesContainer.innerHTML = '';
                }
                
                renderMessages(data.messages, false); // Принудительно указываем append=false
                lastMessageId = data.messages.length > 0 ? 
                    Math.max(...data.messages.map(m => m.id)) : 0;
                
                // Для групповых чатов - отображаем информацию о группе
                if (isGroup && data.group) {
                    const group = data.group;
                    // Обновляем заголовок и информацию о группе
                    chatStatus.innerHTML = `<span class="badge bg-info">${group.members} участников</span>`;
                    // Можно добавить дополнительную информацию о группе
                }
                
                // Прокрутка к последнему сообщению
                if (messagesContainer) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
                if (messagesLoading) {
                    messagesLoading.style.display = 'none';
                }

                // Обновляем статус пользователя в заголовке
                if (!isGroup && currentTab === 'private') {
                    const contact = contacts.find(c => c.id === parseInt(chatId));
                    if (contact) {
                        chatStatus.innerHTML = contact.status === 'online' 
                            ? '<span class="online-indicator"></span>В сети' 
                            : `Был(а) ${contact.lastActivity}`;
                    }
                }
            })
            .catch(error => {
                console.error('Ошибка при загрузке сообщений:', error);
                if (messagesLoading) {
                    messagesLoading.style.display = 'none';
                }
                
                showError('Не удалось загрузить сообщения. Пожалуйста, попробуйте еще раз.');
                
                // Отображаем сообщение об ошибке
                if (messagesContainer) {
                    messagesContainer.innerHTML = `
                        <div class="alert alert-danger m-3" role="alert">
                            Не удалось загрузить сообщения. Пожалуйста, попробуйте еще раз.
                        </div>
                    `;
                }
            });
    }

    // Загрузка новых сообщений
    function loadNewMessages(chatId) {
        // Если чат неактивен, ID не валидный, или уже в процессе обновления, не делаем запрос
        if (!currentChatId || !chatId || chatId <= 0 || isUpdatingMessages) return;
        
        isUpdatingMessages = true;
        
        // Определяем URL API в зависимости от типа чата
        const apiUrl = currentTab === 'group' 
            ? `/api/chat-groups/${chatId}/new-messages` 
            : `/api/chats/${chatId}/new-messages`;
        
        axios.get(apiUrl, {
            params: { last_id: lastMessageId }
        })
        .then(response => {
            const data = response.data;
            if (data.messages && data.messages.length > 0) {
                // Фильтруем сообщения, чтобы избежать дублирования
                const existingMessageIds = Array.from(
                    document.querySelectorAll('.message[data-id]')
                ).map(el => parseInt(el.dataset.id));
                
                const newMessages = data.messages.filter(
                    message => !existingMessageIds.includes(message.id)
                );
                
                if (newMessages.length > 0) {
                    newMessages.forEach(message => appendMessage(message));
                    
                    // Обновляем lastMessageId только если получили новые сообщения
                    const maxId = Math.max(...data.messages.map(m => m.id));
                    if (maxId > lastMessageId) {
                        lastMessageId = maxId;
                    }
                    
                    // Уведомления отключены - удаляем вызовы showNotification
                    
                    // Обновляем счетчик непрочитанных сообщений в меню
                    if (typeof window.checkUnreadMessages === 'function') {
                        window.checkUnreadMessages();
                    }
                }
            }
            isUpdatingMessages = false;
        })
        .catch(error => {
            console.error('Ошибка при загрузке новых сообщений:', error);
            isUpdatingMessages = false;
            
            // Если ошибка 404 (пользователь не найден), останавливаем интервал обновления
            if (error.response && error.response.status === 404) {
                if (messageRefreshInterval) {
                    clearInterval(messageRefreshInterval);
                    console.log('Автообновление остановлено - пользователь не найден');
                    
                    // Показываем уведомление пользователю
                    showError('Выбранный контакт недоступен или был удален');
                    
                    // Обновим список контактов, чтобы удалить несуществующего пользователя
                    setTimeout(() => {
                        loadContacts();
                    }, 1000);
                }
            }
        });
    }


    // Отрисовка сообщений
    function renderMessages(messages, append = false) {
        if (!append) {
            messagesContainer.innerHTML = '';
        }
        
        if (messages.length === 0 && !append) {
            messagesContainer.innerHTML = '<div class="text-center text-muted my-5">Нет сообщений</div>';
            return;
        }
        
        messages.forEach(message => {
            // Проверяем, нет ли уже сообщения с таким ID
            if (document.querySelector(`.message[data-id="${message.id}"]`)) {
                return; // Пропускаем, если сообщение уже есть в DOM
            }
            
            appendMessage(message); // Используем функцию appendMessage вместо повторения кода
        });
    }

    // Добавление одного сообщения в чат
    function appendMessage(message) {
        // Проверяем, нет ли уже сообщения с таким ID
        if (document.querySelector(`.message[data-id="${message.id}"]`)) {
            return; // Пропускаем, если сообщение уже есть в DOM
        }
        
        const messageEl = document.createElement('div');
        const isCurrentUser = parseInt(message.sender_id) === window.Laravel.user.id;
        
        messageEl.className = `message ${isCurrentUser ? 'message-sent' : 'message-received'}`;
        messageEl.dataset.id = message.id; // Добавляем ID как атрибут
        
        let messageContent = '';
        
        // Для групповых чатов добавляем имя отправителя
        if (currentTab === 'group' && !isCurrentUser) {
            messageContent += `
                <div class="message-sender">
                    ${message.sender_name || 'Неизвестный пользователь'}
                </div>
            `;
        }
        
        messageContent += `
            <div class="message-content">
                ${message.content ? message.content : ''}
            </div>
        `;
        
        // Добавление вложений, если есть
        if (message.attachments && message.attachments.length > 0) {
            messageContent += '<div class="attachment-list">';
            
            message.attachments.forEach(attachment => {
                // Проверка типа вложения
                if (attachment.type && attachment.type.startsWith('image/')) {
                    messageContent += `
                        <div>
                            <img src="${attachment.url}" alt="${attachment.name}" class="message-attachment" 
                            onclick="window.open('${attachment.url}', '_blank')">
                        </div>
                    `;
                } else {
                    messageContent += `
                        <div class="attachment-file">
                            <i class="bi bi-file-earmark"></i>
                            <a href="${attachment.url}" target="_blank">${attachment.name}</a>
                        </div>
                    `;
                }
            });
            
            messageContent += '</div>';
        }
        
        messageContent += `
            <div class="message-time text-end">
                ${formatDateTime(message.created_at)}
            </div>
        `;
        
        messageEl.innerHTML = messageContent;
        messagesContainer.appendChild(messageEl);
        
        // Прокрутка к последнему сообщению, если пользователь был внизу чата
        if (isScrolledToBottom) {
            scrollToBottom();
        } else {
            // Показываем уведомление о новом сообщении, если пользователь уже проскроллил выше
            showNewMessagesNotifier();
        }
    }

    // Форматирование даты и времени
    function formatDateTime(dateTimeStr) {
        const date = new Date(dateTimeStr);
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);
        
        const isToday = date.toDateString() === today.toDateString();
        const isYesterday = date.toDateString() === yesterday.toDateString();
        
        let formattedDate = '';
        
        if (isToday) {
            formattedDate = 'Сегодня, ';
        } else if (isYesterday) {
            formattedDate = 'Вчера, ';
        } else {
            formattedDate = `${date.getDate().toString().padStart(2, '0')}.${(date.getMonth() + 1).toString().padStart(2, '0')}.${date.getFullYear()}, `;
        }
        
        formattedDate += date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        
        return formattedDate;
    }

    // Получение имени контакта по ID
    function getContactName(contactId) {
        const contact = contacts.find(c => parseInt(c.id) === parseInt(contactId));
        return contact ? contact.name : 'Неизвестный пользователь';
    }

    // Показ уведомления о новом сообщении
    function showNotification(sender, message) {
        // Функция отключена - никаких уведомлений не показываем
        console.debug('Уведомление отключено:', sender, message);
    }

    // Предпросмотр файлов
    function updateFilePreview() {
        filePreviewList.innerHTML = '';
        
        if (selectedFiles.length === 0) {
            filePreview.style.display = 'none';
            return;
        }
        
        filePreview.style.display = 'block';
        
        selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-preview-item';
            
            // Иконка в зависимости от типа файла
            let fileIcon = 'bi-file-earmark';
            if (file.type.startsWith('image/')) {
                fileIcon = 'bi-file-earmark-image';
            } else if (file.type.startsWith('video/')) {
                fileIcon = 'bi-file-earmark-play';
            } else if (file.type.startsWith('audio/')) {
                fileIcon = 'bi-file-earmark-music';
            } else if (file.type === 'application/pdf') {
                fileIcon = 'bi-file-earmark-pdf';
            } else if (file.type.includes('word')) {
                fileIcon = 'bi-file-earmark-word';
            } else if (file.type.includes('excel') || file.type.includes('spreadsheet')) {
                fileIcon = 'bi-file-earmark-excel';
            }
            
            fileItem.innerHTML = `
                <div>
                    <i class="bi ${fileIcon} me-2"></i>
                    <span>${file.name}</span>
                </div>
                <button type="button" class="btn-close" data-index="${index}"></button>
            `;
            
            const removeButton = fileItem.querySelector('.btn-close');
            removeButton.addEventListener('click', () => {
                // Удаляем файл из массива
                selectedFiles.splice(index, 1);
                // Обновляем предпросмотр
                updateFilePreview();
            });
            
            filePreviewList.appendChild(fileItem);
        });
    }

    // Обработка эмодзи
    emojiButton.addEventListener('click', function() {
        emojiPicker.classList.toggle('show');
    });
    
    // Закрытие панели эмодзи при клике вне её
    document.addEventListener('click', function(e) {
        if (!emojiButton.contains(e.target) && !emojiPicker.contains(e.target)) {
            emojiPicker.classList.remove('show');
        }
    });
    
    // Вставка эмодзи в сообщение
    document.querySelectorAll('.emoji-item').forEach(item => {
        item.addEventListener('click', function() {
            messageInput.value += item.textContent;
            messageInput.focus();
            emojiPicker.classList.remove('show');
        });
    });

    // Обработка вложений
    attachmentButton.addEventListener('click', function() {
        fileInput.click();
    });
    
    // Предпросмотр файла при выборе
    fileInput.addEventListener('change', function() {
        selectedFiles = Array.from(fileInput.files);
        updateFilePreview();
    });

    // Удаляем обработчик для кнопки обновления сообщений, так как она больше не нужна

    // Поиск контактов
    searchInput.addEventListener('input', function() {
        const query = searchInput.value.toLowerCase();
        const filteredContacts = contacts.filter(contact => 
            contact.name.toLowerCase().includes(query));
        renderContacts(filteredContacts);
    });
    
    // Обработка ввода текста (для будущей реализации индикатора набора текста)
    messageInput.addEventListener('input', function() {
        if (currentChatId) {
            // В реальном приложении тут можно было бы отправлять событие
            // "пользователь печатает" на сервер
            
            // Сбрасываем предыдущий таймаут
            if (typingTimeout) {
                clearTimeout(typingTimeout);
            }
            
            // Устанавливаем новый таймаут
            typingTimeout = setTimeout(() => {
                // Здесь можно было бы отправлять событие
                // "пользователь перестал печатать" на сервер
            }, 500);
        }
    });
    
    // Обработка клика по уведомлению - убираем функциональность
    if (chatNotification) {
        chatNotification.style.display = 'none'; // Скрываем уведомление навсегда
        chatNotification.addEventListener('click', function() {
            // Пустая функция - уведомления отключены
            chatNotification.style.display = 'none';
        });
    }

    // Обработчик для кнопки показа контактов на мобильных устройствах
    if (showContactsBtn && contactsList) {
        showContactsBtn.addEventListener('click', function() {
            contactsList.classList.toggle('active');
        });
        
        // Обработчик для закрытия списка контактов при клике вне списка на мобильных
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 768 && contactsList.classList.contains('active')) {
                if (!contactsList.contains(e.target) && !showContactsBtn.contains(e.target)) {
                    contactsList.classList.remove('active');
                }
            }
        });
    }

    // Переменная для отслеживания состояния обновления
    let isUpdatingMessages = false;
    
    // Переменная для отслеживания прокрутки
    let isScrolledToBottom = true;
    
    // Функция для проверки, находится ли скролл в нижней части чата
    function checkScrollPosition() {
        if (!messagesContainer) return true;
        
        const tolerance = 50; // Допуск в пикселях
        return messagesContainer.scrollHeight - messagesContainer.scrollTop - messagesContainer.clientHeight < tolerance;
    }
    
    // Функция для прокрутки вниз
    function scrollToBottom() {
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            isScrolledToBottom = true;
            
            // Скрываем уведомление о новых сообщениях
            if (newMessagesNotifier) {
                newMessagesNotifier.style.display = 'none';
            }
        }
    }
    
    // Функция для отображения уведомления о новых сообщениях
    function showNewMessagesNotifier() {
        if (!newMessagesNotifier) {
            newMessagesNotifier = document.createElement('div');
            newMessagesNotifier.className = 'new-messages-notifier';
            newMessagesNotifier.innerHTML = '<i class="bi bi-arrow-down-circle"></i> Новые сообщения';
            newMessagesNotifier.addEventListener('click', scrollToBottom);
            messagesContainer.parentNode.appendChild(newMessagesNotifier);
        }
        newMessagesNotifier.style.display = 'flex';
    }
    
    // Создаем уведомление о новых сообщениях, если его еще нет
    let newMessagesNotifier = null;
    
    // Отслеживаем прокрутку в контейнере сообщений
    if (messagesContainer) {
        messagesContainer.addEventListener('scroll', function() {
            isScrolledToBottom = checkScrollPosition();
            
            // Если пользователь прокрутил в самый низ, скрываем уведомление
            if (isScrolledToBottom && newMessagesNotifier) {
                newMessagesNotifier.style.display = 'none';
            }
        });
    }
    
    // Функция для воспроизведения звука уведомления
    function playNotificationSound() {
        // Звуковые уведомления отключены
        console.debug('Звуковое уведомление отключено');
    }

    // Функция для проверки новых сообщений во всех чатах
    function checkAllChatsForNewMessages() {
        // Проверяем авторизацию перед отправкой запроса
        if (!window.Laravel || !window.Laravel.user) {
            console.warn('Пользователь не авторизован для проверки сообщений');
            return;
        }
        
        // Используем единый универсальный маршрут API вместо выбора в зависимости от вкладки
        const apiUrl = '/api/chats/check-new-messages';
        
        // Получаем CSRF токен для запроса
        const token = document.querySelector('meta[name="csrf-token"]');
        
        axios.get(apiUrl, {
            headers: {
                'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
                'X-Requested-With': 'XMLHttpRequest', // Добавляем заголовок для AJAX запроса
                'Accept': 'application/json'
            },
            withCredentials: true // Важно! Это позволит передавать куки с авторизацией
        })
        .then(response => {
            const data = response.data;
            
            if (data.hasNewMessages) {
                // Обновляем список контактов, чтобы показать непрочитанные сообщения
                loadContacts();
                
                // Уведомления полностью отключены - убираем вызов showNotification
            }
        })
        .catch(error => {
            console.error('Ошибка при проверке новых сообщений:', error);
            
            // Если ошибка авторизации, останавливаем интервал проверки
            if (error.response && error.response.status === 401) {
                if (globalCheckInterval) {
                    clearInterval(globalCheckInterval);
                    globalCheckInterval = null;
                    console.log('Проверка новых сообщений остановлена: пользователь не авторизован');
                    
                    // Можно добавить сообщение пользователю о необходимости перелогиниться
                    if (errorMessageEl) {
                        errorMessageEl.innerHTML = 'Сессия истекла. Пожалуйста, обновите страницу и войдите снова.';
                        errorMessageEl.style.display = 'block';
                    }
                }
            }
        });
    }

    // Настраиваем axios для всех запросов
    axios.defaults.withCredentials = true; // Глобальная настройка для отправки куки при каждом запросе
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    axios.defaults.headers.common['Accept'] = 'application/json';

    // Если есть CSRF-токен, добавляем его ко всем запросам
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    }

    // Инициализация - загрузка контактов
    // Скрываем индикатор загрузки если он по какой-то причине отображается
    if (contactsLoading) {
        contactsLoading.style.cssText = 'display: none !important';
    }
    
    // Проверяем наличие авторизации перед запуском интервалов
    if (window.Laravel && window.Laravel.user) {
        // Инициализация - загрузка контактов
        loadContacts();
        
        // Запускаем интервал проверки новых сообщений во всех чатах
        globalCheckInterval = setInterval(checkAllChatsForNewMessages, 5000); // каждые 5 секунд
        
        // Запускаем интервал автоматического обновления списка контактов
        autoRefreshInterval = setInterval(() => {
            loadContacts();
        }, 30000); // Обновляем список контактов каждые 30 секунд
    } else {
        console.warn('Пользователь не авторизован. Функции чата ограничены.');
    }
    
    // При выходе со страницы очищаем интервалы обновления
    window.addEventListener('beforeunload', function() {
        if (messageRefreshInterval) {
            clearInterval(messageRefreshInterval);
        }
        if (globalCheckInterval) {
            clearInterval(globalCheckInterval);
        }
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
    });

    document.addEventListener('chatTabChanged', function(e) {
        currentTab = e.detail;
        loadContacts(); // Перезагружаем список контактов/групп
    });

    // Загрузка пользователей для модального окна создания группы
    function loadUsersForGroupSelection() {
        const membersList = document.getElementById('group-members-list');
        if (!membersList) return;
        
        membersList.innerHTML = '<div class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm text-primary ме-2" role="status"></div>Загрузка пользователей...</div>';
        
        axios.get('/api/contacts')
            .then(response => {
                const users = response.data;
                membersList.innerHTML = '';
                
                if (users.length === 0) {
                    membersList.innerHTML = '<div class="text-center py-2">Нет доступных пользователей</div>';
                    return;
                }
                
                users.forEach(user => {
                    const userEl = document.createElement('div');
                    userEl.className = 'member-checkbox';
                    userEl.innerHTML = `
                        <input class="form-check-input group-member-checkbox" type="checkbox" 
                               value="${user.id}" id="user-${user.id}">
                        <label class="form-check-label" for="user-${user.id}">
                            <img src="${user.avatar}" alt="${user.name}" />
                            <span>${user.name}</span>
                        </label>
                    `;
                    membersList.appendChild(userEl);
                });
                
                // Добавляем обработчики для чекбоксов
                const checkboxes = document.querySelectorAll('.group-member-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const selectedCount = document.querySelectorAll('.group-member-checkbox:checked').length;
                        // Создаем и диспатчим событие с количеством выбранных участников
                        const event = new CustomEvent('memberSelectionChanged', { 
                            detail: { count: selectedCount } 
                        });
                        document.dispatchEvent(event);
                    });
                });
            })
            .catch(error => {
                console.error('Ошибка при загрузке пользователей:', error);
                membersList.innerHTML = '<div class="text-center text-danger py-2">Ошибка загрузки пользователей</div>';
            });
    }

    // Инициализация превью аватара
    function initAvatarPreview() {
        const avatarPreview = document.getElementById('avatar-preview');
        const previewImg = document.getElementById('preview-img');
        const groupAvatarInput = document.getElementById('group-avatar');
        const selectedCountBadge = document.getElementById('selected-count');
        
        if (!avatarPreview || !previewImg || !groupAvatarInput) return;
        
        // Клик по превью аватара активирует инпут для выбора файла
        avatarPreview.addEventListener('click', function() {
            groupAvatarInput.click();
        });
        
        // Обновление превью при выборе файла
        groupAvatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Обработчик изменения количества выбранных участников
        document.addEventListener('memberSelectionChanged', function(e) {
            if (selectedCountBadge) {
                selectedCountBadge.textContent = e.detail.count;
            }
        });
    }

    // Инициализация создания группы
    const createGroupBtn = document.getElementById('create-group-btn');
    if (createGroupBtn) {
        createGroupBtn.addEventListener('click', function() {
            loadUsersForGroupSelection();
            initAvatarPreview();
            
            // Очищаем форму при открытии
            const groupNameInput = document.getElementById('group-name');
            const groupDescriptionInput = document.getElementById('group-description');
            const previewImg = document.getElementById('preview-img');
            
            if (groupNameInput) groupNameInput.value = '';
            if (groupDescriptionInput) groupDescriptionInput.value = '';
            if (previewImg) previewImg.src = 'storage/avatar/group_default.png';
            
            // Сбрасываем счетчик выбранных участников
            const selectedCountBadge = document.getElementById('selected-count');
            if (selectedCountBadge) selectedCountBadge.textContent = '0';
        });
    }

    // Обработчик сохранения группы
    const saveGroupBtn = document.getElementById('save-group-btn');
    if (saveGroupBtn) {
        saveGroupBtn.addEventListener('click', function() {
            const groupName = document.getElementById('group-name').value.trim();
            const groupDescription = document.getElementById('group-description').value.trim();
            const groupAvatarInput = document.getElementById('group-avatar');
            
            if (!groupName) {
                showError('Введите название группы');
                return;
            }
            
            // Получаем выбранных участников
            const selectedMembers = Array.from(
                document.querySelectorAll('.group-member-checkbox:checked')
            ).map(checkbox => parseInt(checkbox.value));
            
            // Добавляем текущего пользователя
            if (!selectedMembers.includes(window.Laravel.user.id)) {
                selectedMembers.push(window.Laravel.user.id);
            }
            
            if (selectedMembers.length < 2) {
                showError('Выберите хотя бы одного участника помимо себя');
                return;
            }
            
            const formData = new FormData();
            formData.append('name', groupName);
            formData.append('description', groupDescription);
            
            if (groupAvatarInput && groupAvatarInput.files[0]) {
                formData.append('avatar', groupAvatarInput.files[0]);
            }
            
            // Добавляем участников
            selectedMembers.forEach(memberId => {
                formData.append('members[]', memberId);
            });
            
            // Отправляем запрос на создание группы
            axios.post('/api/chat-groups', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
            .then(response => {
                // Правильное закрытие модального окна через Bootstrap API
                const modalEl = document.getElementById('create-group-modal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                    
                    // Удаляем modal-backdrop после закрытия модального окна
                    setTimeout(() => {
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                        // Восстанавливаем прокрутку страницы
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                    }, 300);
                }
                
                // Если активна вкладка групп, обновляем список
                if (currentTab === 'group') {
                    loadContacts();
                }
                
                // Очищаем форму
                document.getElementById('group-name').value = '';
                document.getElementById('group-description').value = '';
                if (groupAvatarInput) {
                    groupAvatarInput.value = '';
                }
                
                // Показываем уведомление об успешном создании
                showNotification('Система', 'Группа успешно создана');
            })
            .catch(error => {
                console.error('Ошибка при создании группы:', error);
                let errorMessage = 'Ошибка при создании группы';
                if (error.response && error.response.data && error.response.data.error) {
                    errorMessage = error.response.data.error;
                }
                showError(errorMessage);
            });
        });
    }

    // Обработчик переключения вкладок
    document.addEventListener('chatTabChanged', function(e) {
        currentTab = e.detail;
        
        // Сбрасываем текущий чат
        currentChatId = null;
        
        // Очищаем интервал обновления
        if (messageRefreshInterval) {
            clearInterval(messageRefreshInterval);
            messageRefreshInterval = null;
        }
        
        // Очищаем список контактов
        contacts = [];
        
        // Скрываем форму отправки сообщения
        messageForm.classList.add('d-none');
        
        // Отображаем/скрываем кнопку создания группы
        const groupActions = document.getElementById('group-actions');
        if (groupActions) {
            groupActions.style.display = currentTab === 'group' ? 'flex' : 'none';
        }
        
        // Обновляем заголовок
        currentChatName.textContent = currentTab === 'private' ? 'Выберите чат' : 'Выберите группу';
        chatStatus.textContent = '';
        
        // Очищаем сообщения
        messagesContainer.innerHTML = `
            <div class="empty-chat">
                <i class="bi bi-chat-dots"></i>
                <p>Выберите ${currentTab === 'private' ? 'контакт' : 'группу'}, чтобы начать общение</p>
            </div>
        `;
        
        try {
            // Гарантируем скрытие индикатора перед загрузкой нового списка
            if (contactsLoading) {
                contactsLoading.style.cssText = 'display: none !important';
            }
            
            // Загружаем список контактов или групп
            loadContacts();
        } catch (err) {
            console.error('Ошибка при переключении вкладки:', err);
            if (contactsLoading) {
                contactsLoading.style.cssText = 'display: none !important';
            }
        }
    });

    // В конце файла добавим общий обработчик для модальных окон
    document.addEventListener('DOMContentLoaded', function() {
        // Добавляем обработчик для всех модальных окон при их скрытии
        document.querySelectorAll('.modal').forEach(modalEl => {
            modalEl.addEventListener('hidden.bs.modal', function() {
                // Удаляем оставшийся backdrop
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                // Восстанавливаем прокрутку страницы
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            });
        });
    });

    // Добавим функцию для обновления сообщений, которую можно вызывать из любого места
    // Она заменит функциональность кнопки обновления
    function refreshMessages() {
        if (currentChatId) {
            loadMessages(currentChatId);
        }
        // Также проверяем все чаты на наличие новых сообщений
        checkAllChatsForNewMessages();
    }

    // Добавляем функцию в глобальный объект window для доступа извне
    window.refreshChatMessages = refreshMessages;

    // Функция для обработки выбора файлов и автоматической отправки
    function handleFileSelect(event) {
        const files = event.target.files;
        if (files.length === 0) return;

        // Создаем превью файлов
        selectedFiles = Array.from(files);
        updateFilePreview(); // Используем существующую функцию для обновления превью
        
        // Автоматически отправляем сообщение с файлами
        sendMessage();
        
        // Очищаем input после отправки
        fileInput.value = '';
    }

    // Обновляем обработчик события выбора файлов
    if (fileInput) {
        fileInput.removeEventListener('change', null); // Удаляем все существующие обработчики
        fileInput.addEventListener('change', handleFileSelect);
    }

    // Функция отправки сообщения (модифицированная)
    function sendMessage() {
        if (currentChatId === null) {
            showError('Пожалуйста, выберите контакт для отправки сообщения');
            return;
        }

        // Получаем текст сообщения
        const messageText = messageInput.value.trim();
        
        // Проверяем, есть ли текст или файлы для отправки
        // Если нет файлов, то требуем наличие текста
        if (messageText === '' && selectedFiles.length === 0) {
            showError('Пожалуйста, введите сообщение или прикрепите файл');
            return;
        }

        // Скрываем индикатор набора текста если он есть
        if (typingIndicator) {
            typingIndicator.style.display = 'none';
        }
        if (typingTimeout) {
            clearTimeout(typingTimeout);
        }

        // Отключаем кнопку отправки во время запроса
        if (sendButton) {
            sendButton.disabled = true;
        }
        
        // Показываем спиннер загрузки
        if (loadingSpinner) {
            loadingSpinner.style.display = 'block';
        }
        
        // Формируем данные формы
        const formData = new FormData();
        formData.append('message', messageText);
        
        // Добавляем файлы в форму
        if (selectedFiles.length > 0) {
            selectedFiles.forEach(file => {
                formData.append('attachments[]', file);
            });
            
            // Показываем индикатор прогресса загрузки если он есть
            if (fileUploadProgress) {
                fileUploadProgress.style.display = 'block';
            }
            if (progressBar) {
                progressBar.style.width = '0%';
            }
        }

        // Определяем URL в зависимости от типа чата (личный или групповой)
        const url = currentTab === 'group' 
            ? `/api/chat-groups/${currentChatId}/messages` 
            : `/api/chats/${currentChatId}/messages`;

        // Получаем CSRF-токен
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        const token = csrfToken ? csrfToken.getAttribute('content') : '';

        // Отправляем запрос с использованием Axios
        axios.post(url, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'X-CSRF-TOKEN': token
            },
            onUploadProgress: function(progressEvent) {
                if (selectedFiles.length > 0 && progressBar) {
                    const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    progressBar.style.width = percentCompleted + '%';
                }
            }
        })
        .then(response => {
            // Очищаем поле ввода и сбрасываем массив выбранных файлов
            messageInput.value = '';
            selectedFiles = [];
            
            // Очищаем превью файлов
            if (filePreviewList) {
                filePreviewList.innerHTML = '';
            }
            if (filePreview) {
                filePreview.style.display = 'none';
            }
            
            // Скрываем индикатор прогресса
            if (fileUploadProgress) {
                fileUploadProgress.style.display = 'none';
            }
            
            // Скрываем спиннер загрузки
            if (loadingSpinner) {
                loadingSpinner.style.display = 'none';
            }
            
            // Включаем кнопку отправки
            if (sendButton) {
                sendButton.disabled = false;
            }
            
            try {
                // Обрабатываем успешный ответ
                const message = response.data.message;
                
                // Дополнительная проверка соответствия чату
                if (currentChatId === parseInt(message.chat_group_id || message.receiver_id)) {
                    // Проверяем, нет ли уже сообщения с таким ID в DOM
                    if (!document.querySelector(`.message[data-id="${message.id}"]`)) {
                        // Добавляем одно сообщение вместо вызова renderMessages
                        appendMessage(message);
                        lastMessageId = message.id;
                        
                        // Обновляем контакт в списке с новым последним сообщением
                        updateContactLastMessage(currentChatId, messageText || 'Вложение');
                    }
                }
                
                hideError();
            } catch (err) {
                console.error('Ошибка при обработке ответа:', err);
            }
        })
        .catch(error => {
            console.error('Ошибка при отправке сообщения:', error);
            
            // Включаем кнопку отправки
            if (sendButton) {
                sendButton.disabled = false;
            }
            
            // Скрываем индикатор прогресса
            if (fileUploadProgress) {
                fileUploadProgress.style.display = 'none';
            }
            
            // Скрываем спиннер загрузки
            if (loadingSpinner) {
                loadingSpinner.style.display = 'none';
            }
            
            // Формируем сообщение об ошибке
            let errorMessage = 'Ошибка при отправке сообщения.';
            if (error.response) {
                if (error.response.data && error.response.data.error) {
                    errorMessage = error.response.data.error;
                } else if (error.response.status === 404) {
                    errorMessage = 'API для отправки сообщений недоступен (404). Пожалуйста, обратитесь к администратору.';
                } else if (error.response.status === 500) {
                    errorMessage = 'Внутренняя ошибка сервера. Пожалуйста, попробуйте позже.';
                }
            }
            
            showError(errorMessage);
        });
    }

    // Обновляем обработчик события для кнопки отправки
    if (messageForm) {
        messageForm.removeEventListener('submit', null); // Удаляем все существующие обработчики
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            sendMessage();
        });
    }

    function updateContactPosition(contactId) {
        // Находим контейнер списка контактов
        const contactsList = document.querySelector('.contacts-list');
        if (!contactsList) return;
        
        // Находим элемент контакта по ID
        const contactItem = document.querySelector(`.contact-item[data-id="${contactId}"]`);
        if (!contactItem) return;
        
        // Перемещаем контакт в начало списка
        contactsList.prepend(contactItem);
        
        // Добавляем маркер непрочитанных сообщений, если его еще нет
        addUnreadMarker(contactItem);
    }
    
    // Функция для добавления маркера непрочитанных сообщений
    function addUnreadMarker(contactItem) {
        // Проверяем, есть ли уже маркер
        let unreadBadge = contactItem.querySelector('.unread-badge');
        
        // Если маркера нет, создаем его
        if (!unreadBadge) {
            unreadBadge = document.createElement('div');
            unreadBadge.className = 'unread-badge';
            unreadBadge.textContent = '1';
            
            // Добавляем стиль для маркера (можно также добавить стили в CSS файл)
            unreadBadge.style.backgroundColor = '#ff4757';
            unreadBadge.style.color = 'white';
            unreadBadge.style.borderRadius = '50%';
            unreadBadge.style.width = '20px';
            unreadBadge.style.height = '20px';
            unreadBadge.style.display = 'flex';
            unreadBadge.style.alignItems = 'center';
            unreadBadge.style.justifyContent = 'center';
            unreadBadge.style.fontSize = '12px';
            unreadBadge.style.fontWeight = 'bold';
            unreadBadge.style.position = 'absolute';
            unreadBadge.style.right = '10px';
            unreadBadge.style.top = '10px';
            
            // Добавляем маркер в элемент контакта
            contactItem.appendChild(unreadBadge);
            
            // Добавляем класс для выделения непрочитанного сообщения
            contactItem.classList.add('unread');
        } else {
            // Если маркер уже есть, увеличиваем счетчик
            const count = parseInt(unreadBadge.textContent) || 0;
            unreadBadge.textContent = count + 1;
        }
    }
    
    // Функция для проверки новых сообщений
    function checkNewMessages() {
        // Используем правильный URL API
        fetch('/api/chats/check-new-messages')
            .then(response => response.json())
            .then(data => {
                if (data.hasNewMessages) {
                    // Если пришло новое сообщение в текущий чат, обновляем сообщения
                    if (currentChatId && data.messages) {
                        const newMsgFromCurrentChat = data.messages.find(msg => 
                            (msg.sender_id == currentChatId && msg.receiver_id == currentUserId) || 
                            (msg.chat_group_id == currentChatId)
                        );
                        
                        if (newMsgFromCurrentChat) {
                            loadMessages(currentChatId);
                        }
                    }
                    
                    // Обновляем положение контактов в списке
                    data.messages.forEach(message => {
                        const contactId = message.chat_group_id || message.sender_id;
                        // Если это не текущий открытый чат, добавляем маркер непрочитанных сообщений
                        if (contactId != currentChatId) {
                            updateContactPosition(contactId);
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Ошибка при проверке новых сообщений:', error);
            });
    }
    
    // Устанавливаем интервал для проверки новых сообщений (например, каждые 10 секунд)
    const checkMessagesInterval = setInterval(checkNewMessages, 10000);
    
    // Функция для очистки маркера непрочитанных сообщений при открытии чата
    function clearUnreadMarker(contactId) {
        const contactItem = document.querySelector(`.contact-item[data-id="${contactId}"]`);
        if (contactItem) {
            const unreadBadge = contactItem.querySelector('.unread-badge');
            if (unreadBadge) {
                unreadBadge.remove();
                contactItem.classList.remove('unread');
            }
        }
    }
    
    // Модифицируем функцию загрузки сообщений, чтобы она очищала маркер непрочитанных сообщений
    function loadMessages(userId) {
        // Очищаем маркер непрочитанных сообщений
        clearUnreadMarker(userId);
        
        // Остальной код функции loadMessages оставляем без изменений
        // ...existing code...
    }
    
    // Добавляем обработчик событий для контактов
    document.querySelectorAll('.contact-item').forEach(item => {
        item.addEventListener('click', function() {
            const contactId = this.dataset.id;
            currentChatId = contactId;
            
            // Очищаем маркер непрочитанных сообщений при клике на контакт
            clearUnreadMarker(contactId);
            
            // Загружаем сообщения
            loadMessages(contactId);
        });
    });
    
    // При получении новых сообщений через WebSocket или другие методы
    function handleNewMessage(message) {
        // Если сообщение не от текущего открытого чата, обновляем положение контакта
        const messageSenderId = message.chat_group_id || message.sender_id;
        if (messageSenderId != currentChatId) {
            updateContactPosition(messageSenderId);
        } else {
            // Если это текущий чат, просто добавляем сообщение в окно чата
            appendMessage(message);
        }
    }

    // Функция для обновления последнего сообщения в списке контактов
    function updateContactLastMessage(contactId, message) {
        try {
            // Находим элемент контакта по ID
            const contactItem = document.querySelector(`.contact-item[data-id="${contactId}"]`);
            if (!contactItem) return;

            // Обновляем текст последнего сообщения
            const messagePreview = contactItem.querySelector('.contact-info .small.text-truncate');
            if (messagePreview) {
                // Ограничиваем длину сообщения, если оно слишком длинное
                const maxLength = 30;
                const previewText = message.length > maxLength ? 
                    message.substring(0, maxLength) + '...' : message;
                messagePreview.textContent = previewText;
            }

            // Перемещаем контакт в начало списка
            const contactsList = contactItem.parentNode;
            if (contactsList) {
                contactsList.prepend(contactItem);
            }
            
            // Обновляем информацию о контакте в массиве contacts
            const contactIndex = contacts.findIndex(c => parseInt(c.id) === parseInt(contactId));
            if (contactIndex !== -1) {
                contacts[contactIndex].lastMessage = message;
                
                // Обновляем последнюю активность
                contacts[contactIndex].lastActivity = 'Сейчас';
            }
        } catch (error) {
            console.error('Ошибка при обновлении последнего сообщения:', error);
        }
    }
});
