
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





document.addEventListener("DOMContentLoaded", () => {
    const inputs = document.querySelectorAll("input.maskphone");
    for (let i = 0; i < inputs.length; i++) {
        const input = inputs[i];
        input.addEventListener("input", mask);
        input.addEventListener("focus", mask);
        input.addEventListener("blur", mask);
    }
    function mask(event) {
        const blank = "+_ (___) ___-__-__";
        let i = 0;
        const val = this.value.replace(/\D/g, "").replace(/^8/, "7").replace(/^9/, "79");
        this.value = blank.replace(/./g, function(char) {
            if (/[_\d]/.test(char) && i < val.length) return val.charAt(i++);
            return i >= val.length ? "" : char;
        });
        if (event.type === "blur") {
            if (this.value.length == 2) this.value = "";
        } else {
            // Добавляем проверку наличия метода setSelectionRange
            if (this.setSelectionRange) {
                this.setSelectionRange(this.value.length, this.value.length);
            }
        }
    }
});

document.addEventListener('DOMContentLoaded', () => {
    try {
        // Используем более общий селектор или ищем по атрибуту data-*
        const elementsToMask = document.querySelectorAll('[data-mask]');
        
        // Проверяем, найдены ли элементы, прежде чем применять маску
        if (elementsToMask && elementsToMask.length > 0) {
            elementsToMask.forEach(element => {
                if (element) {
                    // Безопасно применяем стили, только если элемент существует
                    const maskType = element.getAttribute('data-mask');
                    
                    switch (maskType) {
                        case 'phone':
                            // Применяем маску телефона
                            applyPhoneMask(element);
                            break;
                        case 'date':
                            // Применяем маску даты
                            applyDateMask(element);
                            break;
                        default:
                            // Общий случай для других типов масок
                            if (element.style) {
                                // Безопасно используем свойство style
                            }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Ошибка при применении масок к элементам:', error);
    }
});

// Функция для маски телефона (пример)
function applyPhoneMask(element) {
    if (!element) return;
    
    element.addEventListener('input', function(e) {
        // Логика маски телефона
    });
}

// Функция для маски даты (пример)
function applyDateMask(element) {
    if (!element) return;
    
    element.addEventListener('input', function(e) {
        // Логика маски даты
    });
}

document.addEventListener('DOMContentLoaded', () => {
    try {
        // const element = document.querySelector('.mask-element');
        // if (element && element.style) {
        //     element.style.color = 'red';
        // } else {
        //     console.warn('Элемент с классом ".mask-element" не найден');
        // }

        setTimeout(() => {
            // const maskElement = document.querySelector('.mask-element');
            // if (maskElement && maskElement.style) {
            //     maskElement.style.display = 'none';
            // } else {
            //     console.warn('Элемент с классом ".mask-element" не найден');
            // }
        }, 1000);
    } catch (error) {
        console.error('Ошибка при работе с .mask-element:', error);
    }
});

// Например, если обращаемся к элементу с id "maskElement"
const maskElement = document.getElementById('maskElement');
if (maskElement) {
    // Перестраховка, чтобы не дергаться с null
    maskElement.style.display = 'none';
}

// Добавляем проверку на существование элемента перед обращением к его свойствам
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        setTimeout(function() {
            const element = document.querySelector('.some-element'); // Предполагаемая проблема
            
            if (element) { // Добавляем проверку
                element.style.display = 'block'; // Теперь безопасно
            } else {
                console.log('Элемент не найден в DOM');
            }
        }, 100);
    }, 100);
});

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const element = document.querySelector('.some-element'); // Предполагаемая проблема
        
        if (element) { // Добавляем проверку
            element.style.display = 'block'; // Теперь безопасно
        } else {
            console.log('Элемент не найден в DOM');
        }
    }, 100);
});

// Безопасное обращение к элементу и его свойствам
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const element = document.querySelector('.some-element');
        
        if (element) {
            element.style.display = 'block';
        } else {
            console.log('Элемент не найден в DOM');
        }
    }, 100);
});

// Заменяем прямое обращение к maskElement безопасной проверкой
const safeApplyStyle = (selector, styleProperty, value) => {
    const element = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (element && element.style) {
        element.style[styleProperty] = value;
    } else {
        console.log(`Элемент ${typeof selector === 'string' ? selector : 'указанный'} не найден или не имеет свойства style`);
    }
};

// Безопасное применение стилей к элементам
document.querySelectorAll('[data-style]').forEach(el => {
    if (el && el.dataset && el.dataset.style) {
        try {
            const styles = JSON.parse(el.dataset.style);
            Object.keys(styles).forEach(prop => {
                safeApplyStyle(el, prop, styles[prop]);
            });
        } catch (e) {
            console.warn('Ошибка при применении стилей:', e);
        }
    }
});

document.addEventListener('DOMContentLoaded', () => {
    subscribeToNotifications();
    setInterval(fetchNewMessages, 1000);
});