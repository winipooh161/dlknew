    <!-- Подключение CSS DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <!-- Подключение JS DataTables и jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    
    <div class="admin__module admin__body">
        <h1>Управление пользователями</h1>
        @include('admin.module.fast_link')
        <div class="user-management">
            <table id="userTable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Телефон</th>
                        <th>пароль</th>
                        <th>Статус</th>
                        <th>Брифы</th> <!-- Новый столбец -->
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr data-id="{{ $user->id }}">
                            <td>{{ $user->id }}</td>
                            <td><input type="text" value="{{ $user->name }}" data-id="{{ $user->id }}" data-field="name" onchange="updateUser(this)" /></td>
                            <td><input type="text" value="{{ $user->phone }}" data-id="{{ $user->id }}" data-field="phone" onchange="updateUser(this)" /></td>
                                <td>
                                    <input type="text" value="******" data-id="{{ $user->id }}" data-field="password" onclick="this.value=''" onchange="updateUser(this)" />
                                </td>
                            
                            <td><input type="text" value="{{ $user->status }}" data-id="{{ $user->id }}" data-field="status" onchange="updateUser(this)" /></td>
                            <td>
                                <a href="{{ route('user.briefs', $user->id) }}">Посмотреть брифы</a>
                            </td>
                            <td><button onclick="deleteUser({{ $user->id }})">Удалить</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
    
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            const table = $('#userTable').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/ru.json"
                }
            });
    
            // Пользовательский поиск с учетом значений input
            $.fn.dataTable.ext.search.push(function(settings, searchData, index, rowData, counter) {
                const filterValue = $('#userTable_filter input').val()
            .toLowerCase(); // Значение из поля поиска DataTables
                const rowInputs = $(`#userTable tbody tr:eq(${index}) input`).map(function() {
                    return $(this).val().toLowerCase(); // Собираем значения всех input в строке
                }).get();
    
                // Проверка: хотя бы одно значение в input содержит поисковую строку
                return rowInputs.some(value => value.includes(filterValue));
            });
    
            // Обновление поиска при вводе текста
            $('#userTable_filter input').off('input').on('input', function() {
                table.draw(); // Перерисовка таблицы
            });
        });
    
        function updateUser(input) {
    const userId = input.dataset.id;
    const field = input.dataset.field;
    const value = input.value;

    fetch(`/admin/users/${userId}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            [field]: value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Данные обновлены");
            if (field === "password") {
                input.value = "******"; // Возвращаем маску после обновления
            }
        } else {
            alert("Ошибка при обновлении данных");
        }
    });
}


    
        function deleteUser(userId) {
            if (!confirm("Вы уверены, что хотите удалить пользователя?")) return;
    
            fetch(`/admin/users/${userId}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $(`#userTable tr[data-id="${userId}"]`).remove();
                        alert("Пользователь удален");
                    } else {
                        alert("Ошибка при удалении пользователя");
                    }
                });
        }
    </script>