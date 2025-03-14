<div class="smets wow fadeInLeft" data-wow-duration="1.5s" data-wow-delay="1.5s" id="smets">
    <h1>Вводная информация</h1>
</div>
<div class="block_create">
    <form action="{{ route('estimate.createcoefs') }}" class="create-step_1-form" method="post">
        @csrf
        <div class="div_block_plus_input_estimate">
            <input type="hidden" name="id" value="{{ $estimate->id }}">
            <div class="block_discount">
                <label for="">
                    <div class="block_abs_redact">
                        <svg width="19" height="18" viewBox="0 0 19 18" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M17.7284 2.49381L16.1096 0.875058C15.7038 0.487649 15.1644 0.271484 14.6034 0.271484C14.0423 0.271484 13.5029 0.487649 13.0971 0.875058L2.28462 11.6813C2.20467 11.7626 2.14848 11.8642 2.12212 11.9751L0.872115 16.9751C0.845659 17.0796 0.846721 17.1893 0.875197 17.2933C0.903672 17.3974 0.958593 17.4923 1.03462 17.5688C1.09302 17.6267 1.16228 17.6726 1.23842 17.7037C1.31457 17.7348 1.39611 17.7505 1.47837 17.7501C1.52818 17.7561 1.57855 17.7561 1.62837 17.7501L6.62837 16.5001C6.73927 16.4737 6.84085 16.4175 6.92212 16.3376L17.7284 5.50631C17.9268 5.30885 18.0842 5.07414 18.1917 4.81565C18.2991 4.55716 18.3544 4.27999 18.3544 4.00006C18.3544 3.72013 18.2991 3.44296 18.1917 3.18447C18.0842 2.92598 17.9268 2.69127 17.7284 2.49381ZM3.60962 12.1251L12.1034 3.63131L14.9721 6.50006L6.47837 14.9938L3.60962 12.1251ZM3.06587 13.3751L5.22837 15.5376L2.33462 16.2688L3.06587 13.3751ZM16.8471 4.62506L15.8534 5.61881L12.9846 2.75006L13.9784 1.75631C14.1476 1.59723 14.3711 1.50866 14.6034 1.50866C14.8356 1.50866 15.0591 1.59723 15.2284 1.75631L16.8471 3.37506C17.012 3.54128 17.1045 3.76593 17.1045 4.00006C17.1045 4.23419 17.012 4.45883 16.8471 4.62506Z"
                                fill="#01ACFF" />
                        </svg>
                    </div>
                    <input type="text" name="discount" id="discount" class="create_coefs-input numer"
                        placeholder="Скидка, %" value="" maxlength="3" required>
                </label>
                <label for="">
                    <div class="block_abs_redact">
                        <svg width="19" height="18" viewBox="0 0 19 18" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M17.7284 2.49381L16.1096 0.875058C15.7038 0.487649 15.1644 0.271484 14.6034 0.271484C14.0423 0.271484 13.5029 0.487649 13.0971 0.875058L2.28462 11.6813C2.20467 11.7626 2.14848 11.8642 2.12212 11.9751L0.872115 16.9751C0.845659 17.0796 0.846721 17.1893 0.875197 17.2933C0.903672 17.3974 0.958593 17.4923 1.03462 17.5688C1.09302 17.6267 1.16228 17.6726 1.23842 17.7037C1.31457 17.7348 1.39611 17.7505 1.47837 17.7501C1.52818 17.7561 1.57855 17.7561 1.62837 17.7501L6.62837 16.5001C6.73927 16.4737 6.84085 16.4175 6.92212 16.3376L17.7284 5.50631C17.9268 5.30885 18.0842 5.07414 18.1917 4.81565C18.2991 4.55716 18.3544 4.27999 18.3544 4.00006C18.3544 3.72013 18.2991 3.44296 18.1917 3.18447C18.0842 2.92598 17.9268 2.69127 17.7284 2.49381ZM3.60962 12.1251L12.1034 3.63131L14.9721 6.50006L6.47837 14.9938L3.60962 12.1251ZM3.06587 13.3751L5.22837 15.5376L2.33462 16.2688L3.06587 13.3751ZM16.8471 4.62506L15.8534 5.61881L12.9846 2.75006L13.9784 1.75631C14.1476 1.59723 14.3711 1.50866 14.6034 1.50866C14.8356 1.50866 15.0591 1.59723 15.2284 1.75631L16.8471 3.37506C17.012 3.54128 17.1045 3.76593 17.1045 4.00006C17.1045 4.23419 17.012 4.45883 16.8471 4.62506Z"
                                fill="#01ACFF" />
                        </svg>
                    </div>
                    <input type="text" name="coefficient" id="coefficient" class="create_coefs-input "
                        placeholder="Коеффициент" value="" maxlength="3" required>
                </label>
            </div>
            <script>
                // Найти поле ввода по его идентификатору
                var inputField = document.getElementById('coefficient');
                // Добавить обработчик события на изменение поля ввода
                inputField.addEventListener('input', function(event) {
                    // Получить введенное значение
                    var inputValue = event.target.value;
                    // Проверить, является ли введенное значение числом
                    if (!isNaN(inputValue)) {
                        // Если значение является числом, обновить поле ввода со значением
                        event.target.value = inputValue;
                    } else {
                        // Если значение не является числом, удалить все символы, кроме цифр и точки
                        event.target.value = inputValue.replace(/[^0-9.]/g, '');
                    }
                });
            </script>
            {{-- <label for="">
                        <p>Наценка</p>
                        <input type="text" name="extra_charge" id="" class="create_coefs-input numer"
                            placeholder="10" maxlength="5" value="1" required>
                    </label> --}}
        </div>
        <div class="div_block_input_estimate">
            {{-- <div class="flex_block_estimate">
                        <p>Договор</p> <input type="text" name="dog" id="" class="create_coefs-input"
                            placeholder="" maxlength="10"> 
                    </div> --}}
            <div class="flex_block_estimate">
                <input type="text" name="dog_num" id="" class="create_coefs-input numer"
                    placeholder="Номер договора" maxlength="10">
            </div>
            <div class="flex_block_estimate">
                <input type="text" name="dog_dop" id="" class="create_coefs-input numer"
                    placeholder="Номер приложения" maxlength="10">
            </div>
            <div class="flex_block_estimate">
                <input type="text" name="act" id="" class="create_coefs-input" placeholder="Акт"
                    maxlength="10">
            </div>
            <div class="flex_block_estimate ">
                <input type="text" name="date" id="date-input" class="create_coefs-input  dates"
                    placeholder="Дата подписания" maxlength="10">
            </div>
            <div class="flex_block_estimate">
                <input type="text" name="address" id="" class="create_coefs-input"
                    placeholder=" Адрес объекта" maxlength="77">
            </div>
            <div class="flex_block_estimate">
                <input type="text" name="phone" id="" class="create_coefs-input maskphone"
                    placeholder="+7(900) 777-00-11" maxlength="25">
            </div>
        </div>
        <button class="create-step_1-submit">Создать смету</button>
    </form>
</div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var inputs = document.querySelectorAll("input.maskphone");
        for (var i = 0; i < inputs.length; i++) {
            var input = inputs[i];
            input.addEventListener("input", mask);
            input.addEventListener("focus", mask);
            input.addEventListener("blur", mask);
        }
        function mask(event) {
            var blank = "+_ (___) ___-__-__";
            var i = 0;
            var val = this.value.replace(/\D/g, "").replace(/^8/, "7").replace(/^9/, "79");
            this.value = blank.replace(/./g, function(char) {
                if (/[_\d]/.test(char) && i < val.length) return val.charAt(i++);
                return i >= val.length ? "" : char;
            });
            if (event.type == "blur") {
                if (this.value.length == 2) this.value = "";
            } else {
                setCursorPosition(this, this.value.length);
            }
        }
        function setCursorPosition(elem, pos) {
            elem.focus();
            if (elem.setSelectionRange) {
                elem.setSelectionRange(pos, pos);
                return;
            }
            if (elem.createTextRange) {
                var range = elem.createTextRange();
                range.collapse(true);
                range.moveEnd("character", pos);
                range.moveStart("character", pos);
                range.select();
                return;
            }
        }
    });
</script>
<script>
    const numerInputs = document.querySelectorAll('.numer');
    let discountInput = document.getElementById("discount");
    // Добавляем обработчик события ввода для каждого элемента
    numerInputs.forEach(input => {
        input.addEventListener('input', () => {
            // Получаем текущее значение поля ввода
            const value = input.value;
            // Удаляем все буквы из значения поля ввода
            const sanitizedValue = value.replace(/[^\d]/g, '');
            // Устанавливаем отфильтрованное значение обратно в поле ввода
            input.value = sanitizedValue;
        });
    });
    discountInput.addEventListener("input", function() {
        // Получаем текущее значение поля ввода
        let value = discountInput.value;
        // Проверяем, что значение не пустое и не содержит уже символ "%"
        if (value && !value.endsWith("%")) {
            // Добавляем символ "%" в конец значения
            discountInput.value = value + "%";
        }
    });
</script>
<script>
    // Получение элемента ввода даты
    const dateInput = document.getElementById('date-input');
    // Добавление обработчика события ввода
    dateInput.addEventListener('input', function() {
        const inputValue = this.value;
        // Удаление всех нецифровых символов из введенного значения
        const numericValue = inputValue.replace(/\D/g, '');
        // Добавление точек сразу после ввода двух символов
        let formattedValue = numericValue;
        if (numericValue.length >= 2) {
            formattedValue = numericValue.slice(0, 2) + '.' + numericValue.slice(2);
        }
        if (numericValue.length >= 4) {
            formattedValue = formattedValue.slice(0, 5) + '.' + formattedValue.slice(5);
        }
        // Установка отформатированного значения в элемент ввода
        this.value = formattedValue;
    });
</script>
