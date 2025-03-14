<h1>{{ $title_site }}</h1>

<form action="{{ route('admin.brief.updateCommon', $brief->id) }}" method="POST">
    @csrf
    @method('PUT')

    <table>
        <tr>
            <td><label for="title">Название:</label></td>
            <td><input type="text" name="title" value="{{ $brief->title }}" required></td>
        </tr>

        <tr>
            <td><label for="description">Описание:</label></td>
            <td><textarea name="description">{{ $brief->description }}</textarea></td>
        </tr>

        <tr>
            <td><label for="price">Общая сумма:</label></td>
            <td><input type="number" name="price" value="{{ $brief->price }}"></td>
        </tr>

        <tr>
            <td><label for="status">Статус:</label></td>
            <td>
                <select name="status" required>
                    <option value="active" {{ $brief->status == 'Активный' ? 'selected' : '' }}>Активный</option>
                    <option value="inactive" {{ $brief->status == 'Завершенный' ? 'selected' : '' }}>Неактивный</option>
                    <option value="completed" {{ $brief->status == 'completed' ? 'selected' : '' }}>Завершен</option>
                </select>
            </td>
        </tr>
    </table>

    <button type="submit">Сохранить</button>
</form>
