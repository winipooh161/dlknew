document.addEventListener('DOMContentLoaded', function() {
    // Функция для открытия модального окна
    const openModal = (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            // Используем setTimeout, чтобы убедиться, что изменение display вступило в силу
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
        } else {
            console.error(`Модальное окно с id ${modalId} не найдено.`);
        }
    };
    // Функция для закрытия модального окна
    const closeModal = (modal) => {
        if (modal) {
            modal.classList.remove('show');
            // Ждем завершения анимации перед скрытием модального окна
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300); // Должно соответствовать длительности перехода в CSS
        }
    };
    // Найти все кнопки с классом 'open-modal-btn'
    const openModalButtons = document.querySelectorAll('.open-modal-btn');
    // Добавить обработчик события для открытия модального окна
    openModalButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const modalId = this.getAttribute('data-modal');
            openModal(modalId);
        });
    });
    // Закрытие модальных окон по нажатию на элементы с классом 'close-modal'
    const closeModalButtons = document.querySelectorAll('.close-modal');
    closeModalButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            closeModal(modal);
        });
    });
    // Закрытие по клику вне модального окна
    window.addEventListener('click', function(event) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (event.target === modal) {
                closeModal(modal);
            }
        });
    });
});