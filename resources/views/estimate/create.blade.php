<div class="smets wow fadeInLeft" data-wow-duration="1.5s" data-wow-delay="1.5s" id="smets">
    <h1>Создание сметы</h1>
</div>
        <form action="{{ route('estimate.save') }}/{{ $estimate->id }}" class="create-step_2-form wow fadeInLeft" data-wow-duration="1.5s" data-wow-delay="1.5s" method="post">
            @csrf
            <div class="block_one">
                <h5>Выберите категорию:</h5>
                <div class="podl_block_one">
                @foreach ($services as $service)
                    @if ($service->type == 'stage')
                        <div class="row service-row label__categories">
                            @php 
                                $stage = $service->info;
                            @endphp
                            <input type="checkbox" id="check-{{ $service->id }}" class="create-checkbox_stage"
                                onchange="toggleElements('check-{{ $service->id }}', 'counter-{{ $service->id }}', 'stage-{{ $service->id }}', '.content-{{ $service->id }}')">
                            <label for="check-{{ $service->id }}">
                                <div class="abs_block_listrs">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M16.9457 2.49381L15.3374 0.875058C14.9343 0.487649 14.3983 0.271484 13.8409 0.271484C13.2835 0.271484 12.7476 0.487649 12.3444 0.875058L1.602 11.6813C1.52258 11.7626 1.46675 11.8642 1.44055 11.9751L0.198652 16.9751C0.172367 17.0796 0.173422 17.1893 0.201713 17.2933C0.230004 17.3974 0.284569 17.4923 0.360099 17.5688C0.418121 17.6267 0.486932 17.6726 0.562587 17.7037C0.638242 17.7348 0.719253 17.7505 0.800974 17.7501C0.85047 17.7561 0.900506 17.7561 0.950002 17.7501L5.9176 16.5001C6.02779 16.4737 6.12871 16.4175 6.20945 16.3376L16.9457 5.50631C17.1428 5.30885 17.2992 5.07414 17.406 4.81565C17.5127 4.55716 17.5677 4.27999 17.5677 4.00006C17.5677 3.72013 17.5127 3.44296 17.406 3.18447C17.2992 2.92598 17.1428 2.69127 16.9457 2.49381ZM2.91841 12.1251L11.3571 3.63131L14.2073 6.50006L5.76858 14.9938L2.91841 12.1251ZM2.37819 13.3751L4.52668 15.5376L1.65168 16.2688L2.37819 13.3751ZM16.0701 4.62506L15.0828 5.61881L12.2327 2.75006L13.22 1.75631C13.3881 1.59723 13.6102 1.50866 13.8409 1.50866C14.0717 1.50866 14.2938 1.59723 14.4619 1.75631L16.0701 3.37506C16.234 3.54128 16.3259 3.76593 16.3259 4.00006C16.3259 4.23419 16.234 4.45883 16.0701 4.62506Z" fill="#424242"/>
                                        </svg>
                                </div>
                                <p class="p_block_stage ">{{ $stage }}</p>
                            </label>
                            <input type="hidden" id="stage-{{ $service->id }}" name="stage-{{ $service->id }}"
                                value="stage-{{ $service->info }} " disabled>
                                <h3 hidden  class="service-h3 none" id="counter-{{ $service->id }}" hidden>
                                    {{ $stage }} </h3>
                        </div> 
                    @endif
                @endforeach
            </div>
            </div>
            <div class="div_container_two_lists" id="div_container_two_lists">
                <h5>Выберите или измените этап:</h5>
                <div class="back_listrs">
                @foreach ($services as $service)
                    @if ($service->type == 'stage')
                        @php
                            $stage = $service->info;
                        @endphp
                        @foreach ($services as $innerService)
                            @if ($innerService->type == 'service' && $innerService->stage == $stage)
                                @php
                                    $price = $innerService->price * $estimate->coefficient * (1 - $estimate->discount / 100);
                                @endphp
                                  <h3 hidden class="service-h3" id="counter-{{ $service->id }}">
                                    {{ $stage }} 
                                         </h3>
                                <div class="div_gop content-{{ $service->id }}" id="div_gop" >
                                    <div class="col-12 service-col-12  hp_input_prise">
                                        <input type="checkbox" id="check-{{ $innerService->id }}"
                                            class="create-checkbox_stage"
                                            onchange="toggleElements('check-{{ $innerService->id }}', 'service-{{ $innerService->id }}', 'counter-{{ $innerService->id }}')">
                                        <label for="check-{{ $innerService->id }}"><p>{{ $innerService->info }}
                                            ({{ $innerService->substage }})   </p><div class="abs_hp_input_prise">
                                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M16.9457 2.49381L15.3374 0.875058C14.9343 0.487649 14.3983 0.271484 13.8409 0.271484C13.2835 0.271484 12.7476 0.487649 12.3444 0.875058L1.602 11.6813C1.52258 11.7626 1.46675 11.8642 1.44055 11.9751L0.198652 16.9751C0.172367 17.0796 0.173422 17.1893 0.201713 17.2933C0.230004 17.3974 0.284569 17.4923 0.360099 17.5688C0.418121 17.6267 0.486932 17.6726 0.562587 17.7037C0.638242 17.7348 0.719253 17.7505 0.800974 17.7501C0.85047 17.7561 0.900506 17.7561 0.950002 17.7501L5.9176 16.5001C6.02779 16.4737 6.12871 16.4175 6.20945 16.3376L16.9457 5.50631C17.1428 5.30885 17.2992 5.07414 17.406 4.81565C17.5127 4.55716 17.5677 4.27999 17.5677 4.00006C17.5677 3.72013 17.5127 3.44296 17.406 3.18447C17.2992 2.92598 17.1428 2.69127 16.9457 2.49381ZM2.91841 12.1251L11.3571 3.63131L14.2073 6.50006L5.76858 14.9938L2.91841 12.1251ZM2.37819 13.3751L4.52668 15.5376L1.65168 16.2688L2.37819 13.3751ZM16.0701 4.62506L15.0828 5.61881L12.2327 2.75006L13.22 1.75631C13.3881 1.59723 13.6102 1.50866 13.8409 1.50866C14.0717 1.50866 14.2938 1.59723 14.4619 1.75631L16.0701 3.37506C16.234 3.54128 16.3259 3.76593 16.3259 4.00006C16.3259 4.23419 16.234 4.45883 16.0701 4.62506Z" fill="#424242"/>
                                                    </svg>
                                            </div>
                                        </label> 
                                        <input type="hidden" id="service-{{ $innerService->id }}"
                                            name="service-{{ $innerService->id }}" data-price='{{ $price }}'
                                            value="service-{{ $innerService->id }}_price-0" disabled>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>
            </div> 
            <div class="div_container_two_lists" id="div_container_two_lists">
                <h5>Редактирование услуги:</h5>
                <div class="div_container_two_lists_block">
                    @foreach ($services as $service)
                        @if ($service->type == 'stage')
                            @php
                                $stage = $service->info;
                            @endphp
                            @foreach ($services as $innerService)
                                @if ($innerService->type == 'service' && $innerService->stage == $stage)
                                    @php
                                        $price = $innerService->price * $estimate->coefficient * (1 - $estimate->discount / 100);
                                    @endphp
                                   <div class="flex_wrap service-flex_wrap reda"  name="{{ $innerService->unit }} "
                                    id="counter-{{ $innerService->id }}">
                                    <p id="service-{{ $innerService->id }}" class="create-service_input"
                                        data-price='{{ $price }}'>
                                        Стоимость: <span id="price-{{ $innerService->id }}"
                                            class="create-service_input"> <span
                                                class="create-service_input">{{ $price }}</span></span>
                                    </p>
                                    <input type="text"
                                        name="counter-{{ $innerService->id }}_info-{{ $innerService->info }}, {{ $innerService->unit }} "
                                        id="counter-{{ $innerService->id }}" class="create-service_input-data"
                                        placeholder="Количество"
                                        oninput="updatePrice({{ $innerService->id }}, {{ $price }})"
                                         value='0'> <span
                                        class="create-input_service-span">{{ $innerService->unit }}</span>
                                </div>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>
            </div> 
            <div class="wd_button">
                <div class="port">
                    <button type="submit" class="send_save button_cret_smet" id="send_save">Превью сметы</button>
                    <input onkeyup="searchOnPage()" type="text" id="search-input" class="pcinput" placeholder="Поиск">
                    <button type="button" class="btn_clear_checkbox" onclick="clearCheckboxes()">Очистить</button>
                    {{-- <input onkeyup="searchOnPage()" type="text" id="search-input" class="mobileinput" placeholder="Поиск"> --}}
                </div>
            </div>
        </form>
    <script>
        function clearCheckboxes() {
        document.querySelectorAll('.create-checkbox_stage').forEach(checkbox => {
            checkbox.checked = false;
            checkbox.dispatchEvent(new Event('change')); // Обновляем состояние
        });
        document.querySelectorAll('.create-service_input-data').forEach(input => {
            input.value = '';
        });
    }
// Обновление цены на основе ввода количества
function updatePrice(serviceId, initialPrice) {
    const counterInput = document.getElementById(`counter-${serviceId}`);
    const priceSpan = document.getElementById(`price-${serviceId}`);
    const priceInput = document.getElementById(`service-${serviceId}`);
    // Преобразование ввода пользователя в число
    const counterValue = parseFloat(counterInput.value.replace(',', '.')) || 0;
    if (counterValue < 0) {
        alert("Количество не может быть отрицательным.");
        counterInput.value = 0; // Сбрасываем значение на 0
        return;
    }
    // Расчет итоговой цены
    const updatedPrice = (counterValue * initialPrice).toFixed(2); // Округляем до 2-х знаков
    // Обновление отображаемой цены и скрытого значения
    priceSpan.innerText = updatedPrice;
    priceInput.value = `service-${serviceId}_price-${updatedPrice}`;
}
    </script>
    <script>
        function toggleElements(checkboxId, serviceId, counterId, contentId = null) {
            let checkbox = document.getElementById(checkboxId);
            let service = document.getElementById(serviceId);
            let counter = document.getElementById(counterId);
            let contents = contentId ? document.querySelectorAll(contentId) : null;
            if (checkbox.checked) {
                service.classList.add('content-visible');
                service.disabled = false;
                if (contents) {
                    contents.forEach(content => {
                        content.classList.add('content-visible');
                    });
                }
                if (counter) {
                    counter.disabled = false;
                }
            } else {
                if (service.classList.contains('content-visible')) {
                    service.classList.remove('content-visible');
                }
                service.disabled = true;
                if (contents) {
                    contents.forEach(content => {
                        if (content.classList.contains('content-visible')) {
                            content.classList.remove('content-visible');
                        }
                    });
                }
                counter.disabled = true;
            }
        }
        function activateInput(event, inputId, link) {
            event.preventDefault();
            let input = document.getElementById(inputId);
            input.disabled = false;
            input.focus();
            link.parentElement.style.display = 'none';
        }
    </script>
    <script>
        function searchOnPage() {
            let searchInput = document.getElementById('search-input');
            let labels = document.querySelectorAll('.create-step_2-form label');
            let searchValue = searchInput.value.toLowerCase();
            let searchWords = searchValue.split(' ');
            labels.forEach(label => {
                let p = label.querySelector('p');
                let labelText = p ? p.textContent.toLowerCase() : label.textContent.toLowerCase();
                let isMatch = true;
                if (searchValue === '') {
                    label.parentElement.style.display = 'block';
                    setTimeout(function() {
                        if (searchInput.value.toLowerCase() === '') {
                            window.location.href = window.location.href;
                        }
                    }, 1000);
                } else {
                    searchWords.forEach(word => {
                        if (!labelText.includes(word)) {
                            isMatch = false;
                        }
                    });
                    if (isMatch) {
                        label.parentElement.style.display = 'block';
                    } else {
                        label.parentElement.style.display = 'none';
                    }
                }
            });
        }
    </script>
    <script>
        const clearCheckEstimate = () => {
            sessionStorage.clear();
            location.reload();
        }
    </script>
<script>
    let createServiceInputs = document.querySelectorAll('.div_gop .create-service_input-data');
    createServiceInputs.forEach(input => {
        input.addEventListener('input', function() {
            let inputValue = this.value;
            let numericValue = parseFloat(inputValue);
            if (isNaN(numericValue)) {
                this.value = '';
            } else {
                this.value = numericValue;
            }
        });
    });
</script>
<script>
    let serviceRows = document.querySelectorAll('.service-row');
    serviceRows.forEach(serviceRow => {
        let label = serviceRow.querySelector('label'); 
        let h3 = document.querySelector('.service-h3');
        label.addEventListener('click', function() {
            let hpInputPrises = serviceRow.querySelectorAll('.create-service_input');
            let hasVisibleContent = false;
            hpInputPrises.forEach(hpInputPrise => {
                if (hpInputPrise.classList.contains('content-visible')) {
                    hasVisibleContent = true;
                }
            });
            if (hasVisibleContent) {
                let errorText = document.createElement('p');
                errorText.classList.add('service-h3-error');
                errorText.textContent = 'Есть выбранные услуги';
                serviceRow.insertAdjacentElement('beforebegin', errorText);
                serviceRow.appendChild(errorText);
                setTimeout(function() {
                    errorText.remove();
                }, 2000);
            }
        });
    });
</script>
<script>
    // Функция для сохранения состояния чекбокса в sessionStorage
    function saveCheckboxState(checkbox) {
        sessionStorage.setItem(checkbox.id, checkbox.checked);
    }
    // Функция для удаления состояния чекбокса из sessionStorage
    function removeCheckboxState(checkbox) {
        sessionStorage.removeItem(checkbox.id);
    }
    document.addEventListener('DOMContentLoaded', function() {
        // Функция для восстановления состояния чекбоксов из sessionStorage
        function restoreCheckboxStates() {
            let checkboxes = document.querySelectorAll('.create-checkbox_stage');
            checkboxes.forEach(function(checkbox) {
                let savedState = sessionStorage.getItem(checkbox.id);
                if (savedState === 'true') {
                    checkbox.checked = true;
                    let checkboxId = checkbox.id.replace('check-', '');
                    toggleElements('check-' + checkboxId, 'counter-' + checkboxId, 'stage-' +
                        checkboxId,
                        '.content-' + checkboxId);
                } else {
                    checkbox.checked = false;
                }
            });
        }
        // Сохраняем состояние чекбокса при изменении
        function handleCheckboxChange(checkbox) {
            return function() {
                if (checkbox.checked) {
                    saveCheckboxState(checkbox);
                } else {
                    removeCheckboxState(checkbox);
                }
                let checkboxId = checkbox.id.replace('check-', '');
                toggleElements('check-' + checkboxId, 'counter-' + checkboxId, 'stage-' + checkboxId,
                    '.content-' +
                    checkboxId);
            };
        }
        // Восстанавливаем состояние чекбоксов при загрузке страницы
        let checkboxes = document.querySelectorAll('.create-checkbox_stage');
        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', handleCheckboxChange(checkbox));
        });
        restoreCheckboxStates();
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let form = document.querySelector('.create-step_2-form');
        let button = document.querySelector('.button_cret_smet');
        button.addEventListener('click', function(event) {
            event.preventDefault();
            let checkboxes = document.querySelectorAll('.create-checkbox_stage');
            let checked = false;
            checkboxes.forEach(function(checkbox) {
                if (checkbox.checked) {
                    checked = true;
                    let parent = checkbox.parentElement.parentElement.parentElement;
                    let parentCheckbox = parent.querySelector('.create-checkbox_stage');
                    if (!parentCheckbox.checked && !parent.classList.contains(create - step_2 -
                            form '')) {
                        parentCheckbox.checked = true;
                        parentCheckbox.dispatchEvent(new Event('change'));
                    }
                }
            });
            if (checked) {
                form.submit();
            }
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let textInputs = document.querySelectorAll('input[type="text"]');
        // Save input values in sessionStorage
        textInputs.forEach(function(input) {
            input.addEventListener('input', function() {
                sessionStorage.setItem(input.id, input.value);
            });
        });
        // Set input values from sessionStorage on page load
        textInputs.forEach(function(input) {
            let savedValue = sessionStorage.getItem(input.id);
            if (savedValue) {
                input.value = savedValue;
            }
        });
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let serverData = {!! json_encode($oldServises) !!};
        if (typeof serverData !== 'undefined' && serverData !== null) {
            for (let key in serverData) {
                if (serverData.hasOwnProperty(key)) {
                    let value = serverData[key];
                    if (value.hasOwnProperty('id')) {
                        let checkbox = document.getElementById(`check-${value.id}`);
                        if (checkbox) {
                            checkbox.checked = true;
                            checkbox.dispatchEvent(new Event('change'));
                        }
                    }
                    for (let subKey in value.info) {
                        if (value.info.hasOwnProperty(subKey)) {
                            let subValue = value.info[subKey];
                            let subCheckbox = document.getElementById(`check-${subValue.id}`);
                            if (subCheckbox) {
                                subCheckbox.checked = true;
                                subCheckbox.dispatchEvent(new Event('change'));
                            }
                            let counterInput = document.getElementById(`counter-${subValue.id}`);
                            if (counterInput) {
                                counterInput.value = subValue.count;
                            }
                        }
                    }
                }
            }
        }
    });
</script>
    <script>
          var send_save = document.getElementById('send_save');
          var form = document.querySelector('.create-step_2-form');
    send_save.addEventListener('click', function() {
        // Проверяем, существует ли форма
        if (form) {
            // Отправляем форму
            form.submit();
        }
    });
    </script>
