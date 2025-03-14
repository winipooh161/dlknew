@extends('layouts.app')

@section('content')
<div class="container">
    <div class="main__flex">
        <div class="main__ponel">
            @include('layouts/ponel')
        </div>
        <div class="main__module">
  
            @include('chats.index')
        </div>
    </div>
</div>
@endsection

<!-- Скрытый скрипт для передачи данных Laravel в JS -->
<script>
    window.Laravel = {
        user: @json([
            'id' => $user->id,
            'name' => $user->name,
            // Добавьте другие необходимые данные
        ]),
    };
</script>

