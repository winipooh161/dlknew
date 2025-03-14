
<h1>Создать групповой чат</h1>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<form action="{{ route('chats.group.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div>
        <label for="name">Название чата:</label>
        <input type="text" name="name" id="name" required>
    </div>
    <div>
        <label for="user_ids">Выберите участников:</label>
        <select name="user_ids[]" id="user_ids" multiple required>
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->status }})</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="avatar">Фотография чата (необязательно):</label>
        <input type="file" name="avatar" id="avatar" accept="image/*">
    </div>
    <button type="submit">Создать чат</button>
</form>

