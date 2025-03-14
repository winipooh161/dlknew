<header class="flex  between">
    <div class="header__body flex between">
        
        <div class="header__user flex center">
            @guest
                @if (Route::has('auth.login-code'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('auth.login-code') }}">{{ __('auth.login-code') }}</a>
                    </li>
                @endif
                @if (Route::has('register'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                    </li>
                @endif
            @else
              
                <div class="header__user__name flex exit">
                    <div class="header__user__logo">
                        @php $user = Auth::user(); @endphp
    
                        <a href="{{ url('/profile') }}">
                            <img src="{{ $user->avatar_url ? asset($user->avatar_url) : asset('user/avatar/group_default.svg') }}" alt="Фото пользователя">
    
                            </a>
                    </div>
                    <span>{{ Auth::user()->name }}</span>
                    
                 
                </div>
                <a class="dropdown-item" href="{{ route('logout') }}" 
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                 <img src="/storage/icon/exit.svg" alt="">
             </a>
              
                
            @endguest
        </div>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
    </div>
</header>
