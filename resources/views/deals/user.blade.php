<div class="deals-list deals-list-user">
    <h1>Ваши сделки 
        @if($userDeals->isNotEmpty())
            <p class="status__user__deal">{{ $userDeals->first()->status }}</p>
        @endif
    </h1>
    @if ($userDeals->isNotEmpty())
        @foreach ($userDeals as $deal)
            <div class="deal" id="deal-{{ $deal->id }}">
                <div class="deal__body">
                    <!-- Информация о сделке -->
                    <div class="deal__info">
                        <div class="deal__info__profile">
                            <div class="deal__avatar">
                                <img src="/storage/{{ asset($deal->avatar_path ?? 'avatars/group_default.svg' )  }}" alt="Avatar">
                            </div>
                            <div class="deal__info__title">
                                <h3>{{ $deal->name }}</h3>
                                <p>{{ $deal->description ?? 'Описание отсутствует' }}</p>
                            </div>
                        </div>
                        <div class="deal__status">
                         
                            <h3><p>Сумма сделки:</p> {{ $deal->total_sum ?? 'Отсутствует' }}</h3>
                            @if ($deal->link)
                                <p class="brif__user__deals__view">
                                   
                                    <a href="{{ $deal->link }}">Смотреть бриф</a>
                                </p>
                            @else
                                <p>Бриф не прикреплен</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="deal__container deal__container__modul">
                      
                        
                        <!-- Секция ответственных за сделку -->
                        <div class="deal__responsible">
                            <ul>
                               
                                @if ($deal->users->isNotEmpty())
                                    @foreach ($deal->users as $user)
                                        <li onclick="window.location.href='/profile/view/{{ $user->id }}'" class="deal__responsible__user">
                                            <div class="deal__responsible__avatar">
                                                <img src="{{ $user->avatar_url ?? 'storage/avatars/group_default.svg' }}" alt="Аватар {{ $user->name }}">
                                            </div>
                                            <div class="deal__responsible__info">
                                                <h5>{{ $user->name }}</h5>
                                                <p>{{ $user->status }}</p>
                                            </div>
                                        </li>
                                    @endforeach
                                @else
                                    <li class="deal__responsible__user">
                                        <p>Ответственные не назначены</p>
                                    </li>
                                @endif
                            </ul>
                        </div>

                        @if($deal)
                        @php
                            // Ищем групповой чат, связанный с данной сделкой
                            $groupChat = \App\Models\Chat::where('deal_id', $deal->id)
                                            ->where('type', 'group')
                                            ->first();
                        @endphp
                        
                        @if($groupChat)
                            <div class="deal-chat">
                               
                            
                                @include('chats.index', ['dealChat' => $groupChat])
                            </div>
                        @else
                            <p>Групповой чат для этой сделки не создан.</p>
                        @endif
                    @endif
                    </div><!-- /.deal__container -->
                </div><!-- /.deal__body -->
            </div><!-- /.deal -->
        @endforeach
    @else
        <p>У вас пока нет сделок.</p>
    @endif
</div>
