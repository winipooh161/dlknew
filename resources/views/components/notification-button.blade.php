@auth
<div class="notification-permission-container">
    <button 
        id="enable-notifications" 
        data-notification-permission="true" 
        class="btn btn-sm btn-outline-primary"
        style="margin-left: 10px; padding: 5px 10px; font-size: 14px;"
    >
        <i class="bi bi-bell"></i> Включить уведомления
    </button>
    
    <script>
        document.getElementById('enable-notifications').addEventListener('click', function() {
            if (window.requestNotificationPermission) {
                window.requestNotificationPermission();
                this.textContent = 'Уведомления запрошены...';
                this.disabled = true;
            } else {
                console.error('Функция requestNotificationPermission не найдена');
                this.textContent = 'Ошибка уведомлений';
                this.classList.add('btn-danger');
            }
        });
    </script>
</div>
@endauth
