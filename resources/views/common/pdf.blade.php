<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Общий бриф #{{ $brif->id }}</title>
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
        
        .section-title {
            margin-top: 30px;
            font-size: 18px;
            color: #3498db;
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
    
    <h1>Общий бриф #{{ $brif->id }}</h1>
    
    <table>
        <tr>
            <td><strong>Название:</strong></td>
            <td>{{ $brif->title }}</td>
        </tr>
        <tr>
            <td><strong>Артикль:</strong></td>
            <td>{{ $brif->article }}</td>
        </tr>
        <tr>
            <td><strong>Описание:</strong></td>
            <td>{{ $brif->description }}</td>
        </tr>
        <tr>
            <td><strong>Общая сумма:</strong></td>
            <td>{{ $brif->price }} руб</td>
        </tr>
        <tr>
            <td><strong>Статус:</strong></td>
            <td>{{ $brif->status }}</td>
        </tr>
        <tr>
            <td><strong>Создатель брифа:</strong></td>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <td><strong>Номер клиента:</strong></td>
            <td>{{ $user->phone }}</td>
        </tr>
    </table>
    
    @for ($i = 1; $i <= 15; $i++)
        <h3 class="section-title">{{ $pageTitlesCommon[$i - 1] }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Вопрос</th>
                    <th>Ответ</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($questions[$i]) && is_array($questions[$i]))
                    @foreach ($questions[$i] as $question)
                        @php
                            $field = $question['key'];
                        @endphp
                        @if (isset($brif->$field))
                            <tr>
                                <td>{{ $question['title'] }}</td>
                                <td>{{ $brif->$field }}</td>
                            </tr>
                        @endif
                    @endforeach
                @else
                    <tr>
                        <td colspan="2">Нет данных для этого раздела</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endfor
    
    @if ($brif->documents && is_array(json_decode($brif->documents, true)))
        <h3 class="section-title">Прикрепленные документы</h3>
        <ul>
            @foreach (json_decode($brif->documents, true) as $document)
                <li><a href="{{ asset($document) }}">{{ basename($document) }}</a></li>
            @endforeach
        </ul>
    @endif
    
    <div style="margin-top: 30px; font-size: 12px; color: #777; text-align: center;">
        <p>Дата создания: {{ $brif->created_at }}</p>
        <p>Дата обновления: {{ $brif->updated_at }}</p>
    </div>
</body>
</html>
