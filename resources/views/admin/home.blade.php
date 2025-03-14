<div class="admin__module admin__body">
    <h1>Панель администратора</h1>
   @include('admin.module.fast_link')
    <div class="information_admin">
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>Тип данных</th>
                    <th>Количество</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Количество пользователей</td>
                    <td>{{ $usersCount }}</td>
                </tr>
                <tr>
                    <td>Количество общих брифов</td>
                    <td>{{ $commonsCount }}</td>
                </tr>
                <tr>
                    <td>Количество коммерческих брифов</td>
                    <td>{{ $commercialsCount }}</td>
                </tr>
                <tr>
                    <td>Количество сделок</td>
                    <td>{{ $dealsCount }}</td>
                </tr>
                <tr>
                    <td>Количество смет</td>
                    <td>{{ $estimatesCount }}</td>
                </tr>
            </tbody>
        </table>
    </div>
   
</div>
