
<h1 class="flex">
    Детали  брифа 
</h1>
<table class="table table-bordered" style="">
    <thead>
        <tr style="">
            <th style="">Поле</th>
            <th style="">ОТВЕТ</th>
        </tr>
    </thead>
    <tbody style="margin-bottom: 30px;">
        <tr>
            <td style="">Артикль</td>
            <td style="">{{ $brif->article }}</td>
        </tr>
        <tr>
            <td style="">Название</td>
            <td style="">{{ $brif->title }}</td>
        </tr>
        <tr>
            <td style="">Общая сумма</td>
            <td style="">{{ $brif->price }} руб</td>
        </tr>
        <tr>
            <td style="">Описание</td>
            <td style="">{{ $brif->description }}</td>
        </tr>
        <tr>
            <td style="">Статус</td>
            <td style="">{{ $brif->status }}</td>
        </tr>
        <tr>
            <td style="">Создатель брифа</td>
            <td style="">{{ $user->name }}</td>
        </tr>
        <tr>
            <td style="">Номер клиента</td>
            <td style="">{{ $user->phone }}</td>
        </tr>
    </tbody>
    <tbody>
        {{-- Display questions and answers --}}
        @for ($i = 1; $i <= 15; $i++)
            <tr class="hd-show">
                <td colspan="2" style="">
                    <h3 style="font-size: 16px; margin: 0; text-align:left;">{{ $pageTitlesCommon[$i - 1] }}</h3>
                </td>
            </tr>
            @foreach ($questions[$i] as $question)
                @php
                    $field = $question['key'];
                @endphp
                @if (isset($brif->$field))
                    <tr>
                        <td style="">{{ $question['title'] }}</td>
                        <td style="">{{ $brif->$field }}</td>
                    </tr>
                @endif
            @endforeach
        @endfor
    </tbody>

    <table class="table table-bordered" style="">
        <thead>
            <tr style="">
                <th style="">Название файла</th>
                <th style="">Действие</th>
            </tr>
        </thead>
        <tbody>
            @if ($brif->documents && is_array(json_decode($brif->documents, true)))
                @forelse (json_decode($brif->documents, true) as $document)
                    <tr>
                        <td style="">{{ basename($document) }}</td>
                        <td style="">
                            <a href="{{ asset($document) }}" target="_blank">Скачать</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" style=" text-align: center;">Документов не найдено</td>
                    </tr>
                @endforelse
            @else
                <tr>
                    <td colspan="2" style=" text-align: center;">Документов не найдено</td>
                </tr>
            @endif
        </tbody>
        
    </table>
    <tbody>
        <tr>
            <td style="">Дата создания</td>
            <td style="">{{ $brif->created_at }}</td>
        </tr>
        <tr>
            <td style="">Дата обновления</td>
            <td style="">{{ $brif->updated_at }}</td>
        </tr>
    </tbody>
</table>

<style>
   



 
</style>
