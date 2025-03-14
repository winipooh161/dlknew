<h1>{{ $title_site }}</h1>

<form action="{{ route('admin.brief.updateCommercial', $brief->id) }}" method="POST">
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

        <!-- Loop through the questions (questions, preferences, zones) if available -->
        @foreach ($questions as $section => $questionGroup)
            <tr>
                <td colspan="2">
                    <fieldset>
                        <legend>Раздел {{ $section }}</legend>
                        <table>
                            @foreach ($questionGroup as $question)
                                <tr>
                                    <td><label for="{{ $question['key'] }}">{{ $question['title'] }}</label></td>
                                    <td>
                                        @if ($question['type'] === 'textarea')
                                            <textarea name="questions[{{ $section }}][{{ $question['key'] }}]" placeholder="{{ $question['placeholder'] }}">
                                                {{ old('questions.' . $section . '.' . $question['key'], $brief->questions[$section][$question['key']] ?? '') }}
                                            </textarea>
                                        @elseif ($question['type'] === 'checkbox')
                                            <input type="checkbox" name="questions[{{ $section }}][{{ $question['key'] }}]"
                                                {{ old('questions.' . $section . '.' . $question['key'], $brief->questions[$section][$question['key']] ?? false) ? 'checked' : '' }}>
                                        @endif
                                    </td>
                                </tr>
                                @if (!empty($question['subtitle']))
                                    <tr>
                                        <td colspan="2">
                                            <small>{{ $question['subtitle'] }}</small>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </table>
                    </fieldset>
                </td>
            </tr>
        @endforeach

    </table>

    <button type="submit">Сохранить</button>
</form>
