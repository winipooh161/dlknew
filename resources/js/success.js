document.addEventListener('DOMContentLoaded', function () {
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');
    // Анимация появления сообщения об успехе
    if (successMessage) {
        successMessage.classList.add('show');
        setTimeout(() => {
            successMessage.classList.remove('show');
        }, 5000);
        successMessage.addEventListener('click', () => {
            successMessage.classList.remove('show');
        });
    }
    // Анимация появления сообщения об ошибке
    if (errorMessage) {
        errorMessage.classList.add('show');
        setTimeout(() => {
            errorMessage.classList.remove('show');
        }, 5000);
        errorMessage.addEventListener('click', () => {
            errorMessage.classList.remove('show');
        });
    }
});
