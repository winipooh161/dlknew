@extends('layouts.auth')
@section('content')
<div class="container-auth">
    <div class="auth__body flex center">
        <div class="auth__form">
            <h1>Вход</h1>
            <p class="auth__title_sub">Мы рады видеть вас! </br>
                <strong>Войдите</strong> в свою учетную запись</p>
            <form action="{{ route('login.password.post') }}" method="POST">
                @csrf
                <label for="phone">
                    
                    <input type="phone" name="phone" id="phone" class="form-control maskphone"placeholder="Введите телефон"  value="{{ old('phone') }}"  maxlength="50" required>
                    @error('phone')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </label>
                <label for="password">
                    
                    <input type="password" name="password" id="password" placeholder="Пароль"   maxlength="50" class="form-control" required>
                    @error('password')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </label>
                <button type="submit" class="btn btn-primary">Войти</button>
            </form>
            <ul class="auth__form__link">
                <li class="else__auth">---------- или ----------</li>
                <li><a href="{{ route('login.code') }}">Войти по коду</a></li>
                <li><a href="{{ route('register') }}">Регистрация</a></li>
            </ul>
        </div>
    </div>
</div>
@endsection
