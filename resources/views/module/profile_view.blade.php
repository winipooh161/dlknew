
<div class="container">
    <h1>Профиль пользователя: {{ $target->name }}</h1>
    <div class="profile-view flex between">
        <!-- Левая колонка: Аватар -->
        <div class="profile__info center bgfff flex wow fadeInLeft" data-wow-duration="1.5s" data-wow-delay="1.5s">
            <div class="profile__avatar">
                <img src="{{ $target->avatar_url ? asset($target->avatar_url) : asset('storage/avatars/group_default.svg') }}" alt="Фото пользователя">
            </div>
        </div>
        <!-- Правая колонка: Информация о пользователе -->
        <div class="profile__link flex">
            <div class="class-border-profile">
                <h3>{{ $target->name }}</h3>
                <label>
                    <p>Номер телефона</p>
                    <span>{{ $target->phone ?? 'Не указан' }}</span>
                </label>
                <label>
                    <p>Почта</p>
                    <span>{{ $target->email }}</span>
                </label>
                <label>
                    <p>Статус</p>
                    <span>{{ $target->status ?? 'Не установлен' }}</span>
                </label>
                <label>
                    <p>Дата регистрации</p>
                    <span>{{ $target->created_at->format('d.m.Y H:i') }}</span>
                </label>
                <!-- Дополнительные поля в зависимости от статуса -->
                @if($target->status == 'user')
                    <label>
                        <p>Город</p>
                        <span>{{ $target->city ?? 'Не указан' }}</span>
                    </label>
                @elseif($target->status == 'partner')
                    <label>
                        <p>Город</p>
                        <span>{{ $target->city ?? 'Не указан' }}</span>
                    </label>
                    <label>
                        <p>Номер договора</p>
                        <span>{{ $target->contract_number ?? 'Не указан' }}</span>
                    </label>
                    <label>
                        <p>Комментарий</p>
                        <span>{{ $target->comment ?? 'Не указан' }}</span>
                    </label>
                @elseif($target->status == 'executor')
                    <label>
                        <p>Город/Часовой пояс</p>
                        <span>{{ $target->city ?? 'Не указан' }}</span>
                    </label>
                    <label>
                        <p>Ссылка на портфолио</p>
                        <span><a href="{{ $target->portfolio_link }}">{{ $target->portfolio_link ?? 'Не указана' }}</a></span>
                    </label>
                    <label>
                        <p>Стаж</p>
                        <span>{{ $target->experience ?? 'Не указан' }}</span>
                    </label>
                    <label>
                        <p>Рейтинг в ЭД</p>
                        <span>{{ $target->rating ?? 'Не указан' }}</span>
                    </label>
                    <label>
                        <p>Проекты в работе</p>
                        <span>{{ $target->active_projects_count ?? '0' }}</span>
                    </label>
                @elseif($target->status == 'coordinator')
                    <label>
                        <p>Стаж</p>
                        <span>{{ $target->experience ?? 'Не указан' }}</span>
                    </label>
                    <label>
                        <p>Рейтинг в ЭД</p>
                        <span>{{ $target->rating ?? 'Не указан' }}</span>
                    </label>
                @endif
            </div>
        </div>
    </div>
    <div class="mt-4">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Назад</a>
    </div>
</div>

