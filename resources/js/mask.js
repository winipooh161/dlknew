document.addEventListener('DOMContentLoaded', function () {
    const nameInputs = document.querySelectorAll('input[name="name"]');
    
    nameInputs.forEach(input => {
        input.addEventListener('input', function () {
            // Проверяем, имеет ли элемент класс "namedeals"
            if (!this.classList.contains('namedeals')) {
                // Удаляем все символы, которые не являются русскими буквами, пробелами или дефисами
                this.value = this.value.replace(/[^А-Яа-яЁё\s\-]/g, '');
            }
            // Иначе можно добавить другую логику для элементов с классом "namedeals"
        });
    });
});


window.addEventListener('load', () => {
    const loadingScreen = document.getElementById('loading-screen');
    const content = document.getElementById('content');
    setTimeout(() => {
        loadingScreen.classList.add('hidden'); // Применяем класс для анимации исчезновения
        document.body.style.overflow = 'auto'; // Включаем прокрутку
        setTimeout(() => {
            loadingScreen.style.display =
                'none'; // Полностью убираем загрузку после анимации
            content.style.opacity =
                '1'; // Плавно показываем содержимое (контент уже анимируется в CSS)
        }, 1000); // Длительность анимации исчезновения (совпадает с fadeOut)
    }, 1000); // Задержка до начала исчезновения
});

document.addEventListener("DOMContentLoaded", () => {
    // Находим все textarea на странице
    const textareas = document.querySelectorAll("textarea");
    // Применяем обработчик ко всем textarea
    textareas.forEach((textarea) => {
        textarea.addEventListener("input", (event) => {
            // Разрешенные символы: английские, русские буквы, цифры, пробелы, запятые, точки, тире и символ рубля (₽)
            const allowedChars = /^[a-zA-Zа-яА-ЯёЁ0-9\s,.\-₽]*$/;
            const value = event.target.value;
            // Если введены запрещенные символы, удаляем их
            if (!allowedChars.test(value)) {
                event.target.value = value.replace(/[^a-zA-Zа-яА-ЯёЁ0-9\s,.\-₽]/g, "");
            }
        });
    });
});