
    <h1>Брифы пользователя: {{ $user->name }}</h1>
    <div>
        <h2>Общие брифы</h2>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($commonBriefs as $brief)
                    <tr>
                        <td>{{ $brief->id }}</td>
                        <td>{{ $brief->title }}</td>
                        <td>{{ $brief->status }}</td>
                        <td>
                            <a href="{{ route('admin.brief.editCommon', $brief->id) }}">Редактировать</a>


                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div>
        <h2>Коммерческие брифы</h2>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($commercialBriefs as $brief)
                    <tr>
                        <td>{{ $brief->id }}</td>
                        <td>{{ $brief->title }}</td>
                        <td>{{ $brief->status }}</td>
                        <td>
                            <a href="{{ route('admin.brief.editCommercial', $brief->id) }}">Редактировать</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    