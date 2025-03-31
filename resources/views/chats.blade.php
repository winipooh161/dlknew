@extends('layouts.app')

@section('content')
    <div class="main__flex">
        <div class="main__ponel">
            @include('layouts/ponel')
        </div>
        <div class="main__module">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
            
            <div class="container-fluid">
                <div class="row chat-container">
                    <!-- Левая панель: список контактов -->
                    @include('chats.components.contacts-panel')
                    
                    <!-- Правая панель: область чата -->
                    @include('chats.components.chat-panel')
                </div>
            </div>
            
            <!-- Уведомление о новом сообщении -->
            <div class="chat-notification" id="chat-notification" style="display: none;">
                <div class="chat-notification-header mb-1 fw-bold">
                    <i class="bi bi-envelope-fill me-2"></i>Новое сообщение
                </div>
                <div class="chat-notification-body" id="notification-text"></div>
            </div>
            
            <!-- CSRF токен для AJAX запросов -->
            <meta name="csrf-token" content="{{ csrf_token() }}">
            
            <script>
                // Добавляем глобальную переменную Laravel для использования в JS
                window.Laravel = {
                    user: @json(Auth::check() ? [
                        'id' => Auth::id(),
                        'name' => Auth::user()->name,
                        'email' => Auth::user()->email
                    ] : null),
                    csrfToken: "{{ csrf_token() }}",
                    baseUrl: "{{ url('/') }}"
                };
            </script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script src="{{ asset('js/chat.js') }}?v={{ time() }}"></script>
        </div>
    </div>
@endsection


