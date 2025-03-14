<div class="container">
    <h1>{{ $title_site }}</h1>

    @if(isset($deal))
      <p>Логи изменений для сделки: <strong>{{ $deal->name }}</strong> (ID: {{ $deal->id }})</p>
      <a href="{{ route('deal.cardinator') }}" class="btn btn-secondary mb-3">Назад к сделкам</a>
    @endif

    <table id="logsTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>ID сделки</th>
                <th>Пользователь</th>
                <th>Дата изменения</th>
                <th>Изменённые поля</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->id }}</td>
                <td>{{ $log->deal_id }}</td>
                <td>[{{ $log->user_id }}] {{ $log->user_name }}</td>
                <td>{{ $log->created_at->format('d.m.Y H:i:s') }}</td>
                <td>
                    <ul>
                        @foreach($log->changes as $field => $change)
                            <li>
                                <strong>{{ $field }}:</strong>
                                <br>
                                <em>Было:</em> {{ is_array($change['old']) ? json_encode($change['old']) : $change['old'] }}<br>
                                <em>Стало:</em> {{ is_array($change['new']) ? json_encode($change['new']) : $change['new'] }}
                            </li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Подключение CSS DataTables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<!-- Подключение JS DataTables и jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#logsTable').DataTable({
        "order": [[ 3, "desc" ]],
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.11.5/i18n/ru.json"
        }
    });
});
</script>