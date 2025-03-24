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
                            <div class="deal__status-button">
                                @if ($deal->link)
                                <p class="brif__user__deals__view">
                                   
                                    <a href="{{ $deal->link }}">Смотреть бриф</a>
                                </p>
                            @else
                                <p>Бриф не прикреплен</p>
                            @endif
                            <button class="btn-open-chat" onclick="openChatModal({{ $deal->id }})">Открыть чат</button>
                            </div>
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
                        <!-- Контейнер чата -->
                        <div id="chatContainer-{{ $deal->id }}" class="chat-block">
                            @if($groupChat)
                                <div class="deal-chat">
                                    @include('chats.index', ['dealChat' => $groupChat])
                                </div>
                            @else
                                <p>Групповой чат для этой сделки не создан.</p>
                            @endif
                        </div>
                        <!-- Placeholder для возврата чата -->
                        <div id="chatPlaceholder-{{ $deal->id }}"></div>
                        @endif
                    </div><!-- /.deal__container -->
                </div><!-- /.deal__body -->
            </div><!-- /.deal -->
        @endforeach
    @else
        <p>У вас пока нет сделок.</p>
    @endif
</div>

<!-- Модальное окно для чата -->
<div id="chatModal" style="position:fixed; top:0; left:0; width:100%; height:100%; display:none; background:#fff; z-index:9999;">
    <button class="close-deal-modal" style="" onclick="closeChatModal()">&#8592; Вернуться к сделке</button>
    <div id="chatModalContent" style="margin-top:50px; height:calc(100% - 50px); overflow:auto;">
        <!-- Контент чата подставляется динамически -->
    </div>
</div>

<script>
function openChatModal(dealId) {
    if(window.innerWidth <= 767) {
        var chatContainer = document.getElementById('chatContainer-' + dealId);
        var placeholder = document.getElementById('chatPlaceholder-' + dealId);
        if(chatContainer) {
            // Перемещаем чат в модальное окно
            placeholder.appendChild(chatContainer);
            chatContainer.style.display = 'block';
            document.getElementById('chatModalContent').appendChild(chatContainer);
        }
    }
    document.getElementById('chatModal').style.display = 'block';
}
function closeChatModal() {
    document.getElementById('chatModal').style.display = 'none';
    // Возвращаем чат на место, если он был перемещён
    var modalContent = document.getElementById('chatModalContent');
    if(modalContent.children.length) {
        var chatElem = modalContent.children[0];
        var dealId = chatElem.id.split('-')[1]; // Извлекаем id сделки
        var placeholder = document.getElementById('chatPlaceholder-' + dealId);
        if(placeholder) {
            placeholder.appendChild(chatElem);
            chatElem.style.display = 'none'; // скрываем чат на мобильном устройстве
        }
    }
}
</script>
