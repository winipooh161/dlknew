

@extends('layouts.auth')
@section('content')
    <div class="container-auth">
        <div class="auth__body flex center">
            <div class="auth__form">
     
                <h1>{{ $title_site }}</h1>

                <form action="{{ route('auth.complete_registration_by_deal', ['token' => $deal->registration_token]) }}" method="POST">
                    @csrf
                
                        <label for="name">
                        <input type="text" id="name" name="name"placeholder="Имя и фамилия" class="form-control" required>
                    </label>
                
                
                        <label for="phone">
                        <input type="text" id="phone" name="phone" class="form-control maskphone"placeholder="Введите телефон"  value="{{ old('phone') }}" required>
                    </label>
                
                  
                        <label for="password">
                        <input type="password" id="password" name="password" placeholder="Пароль" class="form-control" required>
             
                    </label>
                
                        <label for="password_confirmation">
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Подтвердите пароль"  class="form-control" required>
                    </label>
                
                    <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
                    <ul class="auth__form__link">
                        <li class="else__auth">---------- или ----------</li>
                        <li><a href="{{ route('login.code') }}">Войти по коду</a></li>
                        <li class="politic__info" style="text-align: center">Нажимая на "Зарегистрироваться" вы соглашаетесь с<a href=""> политикой конфиденциальности</a></li>
                    </ul>
                </form>
            </div>
        </div>
    </div>
@endsection
