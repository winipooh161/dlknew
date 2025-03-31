<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Коммерческий бриф #{{ $brif->id }}</title>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path('fonts/DejaVuSans.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        h1 {
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        h2 {
            color: #3498db;
            margin-top: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table, th, td {
            border: 1px solid #ddd;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo img {
            max-width: 150px;
            height: auto;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="logo">
        <img src="{{ storage_path('app/public/icon/fool_logo.svg') }}" alt="Логотип">
    </div>
    
    <h1>Коммерческий бриф #{{ $brif->id }}</h1>
    
    <table>
        <thead>
            <tr>
                <th>Поле</th>
                <th>Значение</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>ID</td>
                <td>{{ $brif->id }}</td>
            </tr>
            <tr>
                <td>Название</td>
                <td>{{ $brif->title }}</td>
            </tr>
            <tr>
                <td>Описание</td>
                <td>{{ $brif->description }}</td>
            </tr>
            <tr>
                <td>Статус</td>
                <td>{{ $brif->status }}</td>
            </tr>
            <tr>
                <td>User ID</td>
                <td>{{ $brif->user_id }}</td>
            </tr>
            <tr>
                <td>Дата создания</td>
                <td>{{ $brif->created_at }}</td>
            </tr>
            <tr>
                <td>Дата обновления</td>
                <td>{{ $brif->updated_at }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Общий бюджет: {{ number_format($brif->price, 2, ',', ' ') }} ₽</h2>

    @if($zones)
        <h2>Бюджет по зонам</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Зона</th>
                    <th>Бюджет</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($zones as $index => $zone)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $zone['name'] ?? 'Без названия' }}</td>
                        <td>
                            @if (isset($price[$index]))
                                {{ number_format($price[$index], 2, ',', ' ') }} ₽
                            @else
                                0 ₽
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Зоны</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Название</th>
                <th>Описание</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($zones as $index => $zone)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $zone['name'] ?? 'Без названия' }}</td>
                    <td>{{ $zone['description'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @foreach ($preferencesFormatted as $zoneName => $zonePreferences)
        <h2>Предпочтения для {{ $zoneName }}</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Вопрос</th>
                    <th>Ответ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($zonePreferences as $index => $preference)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $preference['question'] }}</td>
                        <td>{{ $preference['answer'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    @if($brif->documents)
        <h2>Документы</h2>
        <table>
            <thead>
                <tr>
                    <th>Название файла</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $documents = json_decode($brif->documents, true) ?? [];
                @endphp
                
                @if (!empty($documents) && is_array($documents))
                    @foreach ($documents as $document)
                        <tr>
                            <td><a href="{{ asset($document) }}">{{ basename($document) }}</a></td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>Документов не найдено</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif
</body>
</html>
