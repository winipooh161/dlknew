<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BrifsController;
use App\Http\Controllers\DealFeedController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\CommercialController;
use App\Http\Controllers\DealModalController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\SmetsController;
use App\Http\Controllers\DealsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;
// Используемые контроллеры Firebase удалены

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

// Главная страница
Route::get('/', function () {
    return redirect('login/password');
});

// Стандартные маршруты аутентификации
Auth::routes();

// Группа маршрутов с middleware для аутентификации
Route::middleware('auth')->group(function () {

    // Главная страница
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Поддержка
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::post('/support/reply/{ticket}', [SupportController::class, 'reply'])->name('support.reply');
    Route::post('/support/create', [SupportController::class, 'create'])->name('support.create');

    // Профиль
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/view/{id}', [ProfileController::class, 'viewProfile'])->name('profile.view');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.update_avatar');
    Route::post('/profile/send-code', [ProfileController::class, 'sendVerificationCode'])->name('profile.send-code');
    Route::post('/profile/verify-code', [ProfileController::class, 'verifyCode'])->name('profile.verify-code');
    Route::post('/delete-account', [ProfileController::class, 'deleteAccount'])->name('delete_account');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/update-all', [ProfileController::class, 'updateProfileAll'])->name('profile.update_all');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
    Route::get('/brifs', [BrifsController::class, 'index'])->name('brifs.index');
    Route::post('/brifs/store', [BrifsController::class, 'store'])->name('brifs.store');
    Route::delete('/brifs/{brif}', [BrifsController::class, 'destroy'])->name('brifs.destroy');
    Route::get('/common/questions/{id}/{page}', [CommonController::class, 'questions'])->name('common.questions');
    Route::post('/common/questions/{id}/{page}', [CommonController::class, 'saveAnswers'])->name('common.saveAnswers');
    Route::get('/common/create', [BrifsController::class, 'common_create'])->name('common.create');
    Route::post('/common', [BrifsController::class, 'common_store'])->name('common.store');
    Route::get('/common/{id}', [BrifsController::class, 'common_show'])->name('common.show');
    Route::get('/common/{id}/download-pdf', [BrifsController::class, 'common_download_pdf'])->name('common.download.pdf');
    Route::get('/commercial/questions/{id}/{page}', [CommercialController::class, 'questions'])->name('commercial.questions');
    Route::post('/commercial/questions/{id}/{page}', [CommercialController::class, 'saveAnswers'])->name('commercial.saveAnswers');
    Route::get('/commercial/create', [BrifsController::class, 'commercial_create'])->name('commercial.create');
    Route::post('/commercial', [BrifsController::class, 'commercial_store'])->name('commercial.store');
    Route::get('/commercial/{id}', [BrifsController::class, 'commercial_show'])->name('commercial.show');
    Route::get('/commercial/{id}/download-pdf', [BrifsController::class, 'commercial_download_pdf'])->name('commercial.download.pdf');

    // Сделка для пользователя
    Route::get('/deal-user', [DealsController::class, 'dealUser'])->name('deal.user');
});

Route::middleware(['auth', 'status:partner'])->group(function () {
    Route::get('/estimate', [SmetsController::class, 'estimate'])->name('estimate');
    Route::get('/estimate/service', [SmetsController::class, 'allService'])->name('estimate.service');
    Route::get('/estimate/default', [SmetsController::class, 'defaultValueBD'])->name('estimate.default');
    Route::get('/estimate/create/{id?}', [SmetsController::class, 'createEstimate'])->name('estimate.create');
    Route::post('/estimate/createcoefs', [SmetsController::class, 'addCoefs'])->name('estimate.createcoefs');
    Route::post('/estimate/save/{id?}', [SmetsController::class, 'saveEstimate'])->name('estimate.save');
    Route::post('/estimate/pdf/{id?}', [SmetsController::class, 'savePdf'])->name('estimate.pdf');
    Route::post('/estimate/del/{id}', [SmetsController::class, 'delEstimate'])->name('estimate.del');
    Route::post('/estimate/chenge/{id}/{slot}/{value}/{type}/{stage}', [SmetsController::class, 'changeService'])->name('estimate.change');
    Route::get('/estimate/preview', [SmetsController::class, 'previewEstimate'])->name('estimate.preview');
    Route::get('/estimate/defaultServices', [SmetsController::class, 'defaultServices'])->name('estimate.defaultServices');
    Route::get('/estimate/copy/{id?}', [SmetsController::class, 'copyEstimate'])->name('estimate.copy');
    Route::get('/estimate/change-estimate/{id?}', [SmetsController::class, 'changeEstimate'])->name('estimate.changeEstimate');
});
Route::middleware(['auth', 'status:coordinator,admin,partner,visualizer,architect,designer'])->group(function () {
    Route::get('/deal-cardinator', [DealsController::class, 'dealCardinator'])->name('deal.cardinator');
    // Используйте один маршрут для модального окна:
    
});


Route::middleware(['auth', 'status:coordinator,admin,partner'])->group(function () {
   
    Route::get('/deals/create', [DealsController::class, 'createDeal'])->name('deals.create');
    Route::post('/deal/store', [DealsController::class, 'storeDeal'])->name('deals.store');
    Route::put('/deal/update/{id}', [DealsController::class, 'updateDeal'])->name('deal.update');
    Route::get('/deals/{deal}/edit', [DealsController::class, 'editDeal'])->name('deal.edit');
    Route::put('/deals/{deal}', [DealsController::class, 'updateDeal'])->name('deal.update');
    Route::get('/deal/{deal}/modal', [DealModalController::class, 'getDealModal'])->name('deal.modal');
    
});

Route::middleware(['auth', 'status:coordinator,admin'])->group(function () {
    Route::get('/deal/change-logs', [DealsController::class, 'changeLogs'])->name('deal.change_logs');
    Route::get('/deal/{deal}/change-logs', [DealsController::class, 'changeLogsForDeal'])->name('deal.change_logs.deal');
});

Route::post('/deal/{deal}/feed', [DealFeedController::class, 'store'])
     ->name('deal.feed.store');

Route::get('/deals/{deal}/logs', [DealsController::class, 'changeLogsForDeal'])->name('deal.logs');
Route::get('/deals/logs', [DealsController::class, 'changeLogs'])->name('deal.logs.all');

Route::get('/refresh-csrf', function() {
    return response()->json(['token' => csrf_token()]);
})->name('refresh-csrf');

Route::middleware(['auth', 'status:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin');
    Route::get('/admin/users', [AdminController::class, 'user_admin'])->name('admin.users');
    Route::put('/admin/users/{id}', [AdminController::class, 'update']);
    Route::delete('/admin/users/{id}', [AdminController::class, 'destroy']);
    Route::get('/admin/users/{id}/briefs', [AdminController::class, 'userBriefs'])->name('user.briefs');
    Route::get('/admin/briefs/{id}', [AdminController::class, 'edit'])->name('admin.brief.edit');
    Route::put('/admin/briefs/{id}', [AdminController::class, 'update_brif'])->name('admin.brief.update_brif');
    Route::get('/admin/brief/editCommon/{id}', [AdminController::class, 'editCommonBrief'])->name('admin.brief.editCommon');
    Route::post('/admin/brief/updateCommon/{id}', [AdminController::class, 'updateCommonBrief'])->name('admin.brief.updateCommon');
    Route::get('admin/brief/commercial/{id}/edit', [AdminController::class, 'editCommercialBrief'])->name('admin.brief.editCommercial');
    Route::put('admin/brief/commercial/{id}', [AdminController::class, 'updateCommercialBrief'])->name('admin.brief.updateCommercial');
});

Route::get('/register_by_deal/{token}', [AuthController::class, 'registerByDealLink'])->name('register_by_deal');
Route::post('/complete-registration-by-deal/{token}', [AuthController::class, 'completeRegistrationByDeal'])->name('auth.complete_registration_by_deal');
Route::get('', [AuthController::class, 'showLoginFormByPassword'])->name('login.password');
Route::post('login/password', [AuthController::class, 'loginByPassword'])->name('login.password.post');
Route::get('login/code', [AuthController::class, 'showLoginFormByCode'])->name('login.code');
Route::post('login/code', [AuthController::class, 'loginByCode'])->name('login.code.post');
Route::post('/send-code', [AuthController::class, 'sendCode'])->name('send.code');
Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [AuthController::class, 'register'])->name('register.post');
Route::match(['GET', 'POST'], '/logout', [AuthController::class, 'logout'])->name('logout');

if (app()->environment('production')) {
    URL::forceScheme('https');
}

// Если URL без id, перенаправляем на карточку сделок
Route::get('/deal/update', function () {
    return redirect()->route('deal.cardinator');
});

// Определяем маршрут для обновления сделки (POST), где {id} — идентификатор сделки
Route::post('/deal/update/{id}', [DealsController::class, 'updateDeal'])
    ->name('deal.update');

// Маршрут для отображения модального окна сделки

// Маршруты для чатов
Route::middleware('auth')->prefix('api')->group(function () {
    // Получить список контактов
    Route::get('/contacts', function () {
        $user = auth()->user();
        $contacts = \App\Models\User::where('id', '!=', $user->id)->get()->map(function ($contact) {
            return [
                'id' => $contact->id,
                'name' => $contact->name,
                'avatar' => $contact->avatar_url,
                'status' => rand(0, 1) ? 'online' : 'offline', // Демо-данные
                'lastMessage' => 'Сообщений пока нет',
                'unreadCount' => rand(0, 5), // Демо-данные
                'lastActivity' => 'Недавно',
            ];
        });
        return response()->json($contacts);
    });

    // Получить сообщения чата
    Route::get('/chats/{id}/messages', function ($id) {
        // Здесь будет логика получения сообщений
        return response()->json(['messages' => []]);
    });

    // Отправить сообщение
    Route::post('/chats/{id}/messages', function (Request $request, $id) {
        // Здесь будет логика сохранения сообщения
        return response()->json(['status' => 'success']);
    });

    // Получить количество непрочитанных сообщений
    Route::get('/messages/unread-count', function () {
        $user = auth()->user();
        $count = $user->unreadMessagesCount();
        return response()->json(['count' => $count]);
    });
});

// Маршрут для пропуска страницы в брифе
Route::middleware(['auth'])->group(function () {
    Route::post('/common/brifs/{id}/skip/{page}', [App\Http\Controllers\CommonController::class, 'skipPage'])->name('common.skipPage');
    
    // Маршруты для чатов
    Route::get('/chats', [\App\Http\Controllers\ChatController::class, 'index'])->name('chats.index');
});

// Маршруты API для чатов
Route::middleware('auth')->prefix('api')->group(function () {
    // Получить список контактов
    Route::get('/contacts', [\App\Http\Controllers\ChatController::class, 'getContacts']);

    // Получить сообщения чата
    Route::get('/chats/{id}/messages', [\App\Http\Controllers\ChatController::class, 'getMessages']);
    
    // Получить новые сообщения после указанного ID
    Route::get('/chats/{id}/new-messages', [\App\Http\Controllers\ChatController::class, 'getNewMessages']);

    // Отправить сообщение
    Route::post('/chats/{id}/messages', [\App\Http\Controllers\ChatController::class, 'sendMessage']);
    
    // Получить количество непрочитанных сообщений
    Route::get('/messages/unread-count', [\App\Http\Controllers\ChatController::class, 'getUnreadCount']);
});

// Маршрут для чатов - доступен только для admin, coordinator и partner
Route::get('/chats', [\App\Http\Controllers\ChatController::class, 'index'])
    ->middleware(['auth', 'check.chat.access'])
    ->name('chats.index');

// API маршруты для чатов с проверкой доступа
Route::middleware(['auth', 'check.chat.access'])->prefix('api')->group(function () {
    // Получить список контактов
    Route::get('/contacts', [\App\Http\Controllers\ChatController::class, 'getContacts']);

    // Получить сообщения чата
    Route::get('/chats/{id}/messages', [\App\Http\Controllers\ChatController::class, 'getMessages']);
    
    // Получить новые сообщения после указанного ID
    Route::get('/chats/{id}/new-messages', [\App\Http\Controllers\ChatController::class, 'getNewMessages']);

    // Отправить сообщение
    Route::post('/chats/{id}/messages', [\App\Http\Controllers\ChatController::class, 'sendMessage']);
    
    // Получить количество непрочитанных сообщений
    Route::get('/messages/unread-count', [\App\Http\Controllers\ChatController::class, 'getUnreadCount']);
    
    // Маршруты для групповых чатов
    Route::get('/chat-groups', [\App\Http\Controllers\ChatController::class, 'getChatGroups']);
    Route::post('/chat-groups', [\App\Http\Controllers\ChatController::class, 'createChatGroup']);
    Route::get('/chat-groups/{id}', [\App\Http\Controllers\ChatController::class, 'getChatGroup']);
    Route::put('/chat-groups/{id}', [\App\Http\Controllers\ChatController::class, 'updateChatGroup']);
    Route::delete('/chat-groups/{id}', [\App\Http\Controllers\ChatController::class, 'deleteChatGroup']);
    
    // Управление участниками группы
    Route::post('/chat-groups/{id}/users', [\App\Http\Controllers\ChatController::class, 'addChatGroupUser']);
    Route::delete('/chat-groups/{id}/users/{user_id}', [\App\Http\Controllers\ChatController::class, 'removeChatGroupUser']);
    
    // Сообщения в групповом чате
    Route::get('/chat-groups/{id}/messages', [\App\Http\Controllers\ChatController::class, 'getGroupMessages']);
    Route::post('/chat-groups/{id}/messages', [\App\Http\Controllers\ChatController::class, 'sendGroupMessage']);
    Route::get('/chat-groups/{id}/new-messages', [\App\Http\Controllers\ChatController::class, 'getNewGroupMessages']);
    
    // Поиск по сообщениям
    Route::get('/messages/search', [\App\Http\Controllers\ChatController::class, 'searchMessages']);
});

// Чаты - доступны только для авторизованных пользователей
Route::middleware(['auth', 'update.last.seen'])->group(function () {
    // Маршрут для просмотра чатов
    Route::get('/chats', [ChatController::class, 'index'])->name('chats');
});

// Маршрут для чата
Route::get('/chat', [ChatController::class, 'index'])->name('chat')->middleware(['auth', 'check.chat.access']);

// Маршруты для чата с доступом новым ролям
Route::middleware(['auth'])->group(function () {
    // Чат
    Route::get('/chats', [ChatController::class, 'index'])->name('chats.index');
    
    // Маршруты для API чата (доступны для всех ролей с разрешенным доступом)
    Route::prefix('api')->group(function () {
        Route::get('/contacts', [ChatController::class, 'getContacts']);
        Route::get('/chats/{id}/messages', [ChatController::class, 'getMessages']);
        Route::get('/chats/{id}/new-messages', [ChatController::class, 'getNewMessages']);
        Route::post('/chats/{id}/messages', [ChatController::class, 'sendMessage']);
        Route::get('/chats/check-new-messages', [ChatController::class, 'checkNewMessagesInAllChats']);
        
        // Групповые чаты
        Route::get('/chat-groups', [ChatController::class, 'getChatGroups']);
        Route::post('/chat-groups', [ChatController::class, 'createChatGroup']);
        Route::get('/chat-groups/{id}', [ChatController::class, 'getChatGroup']);
        Route::get('/chat-groups/{id}/messages', [ChatController::class, 'getGroupMessages']);
        Route::get('/chat-groups/{id}/new-messages', [ChatController::class, 'getNewGroupMessages']);
        Route::post('/chat-groups/{id}/messages', [ChatController::class, 'sendGroupMessage']);
    });
});

// Маршруты для чата - доступны только для авторизованных пользователей
Route::middleware(['auth', 'update.last.seen'])->group(function () {
    // Основной маршрут чата
    Route::get('/chats', [ChatController::class, 'index'])->name('chats.index');
    
    // API маршруты для чата (доступны для всех ролей с разрешенным доступом)
    Route::prefix('api')->group(function () {
        // Личные чаты
        Route::get('/contacts', [ChatController::class, 'getContacts']);
        Route::get('/chats/{id}/messages', [ChatController::class, 'getMessages']);
        Route::get('/chats/{id}/new-messages', [ChatController::class, 'getNewMessages']);
        Route::post('/chats/{id}/messages', [ChatController::class, 'sendMessage']);
        Route::get('/chats/check-new-messages', [ChatController::class, 'checkNewMessagesInAllChats']);
        
        // Групповые чаты
        Route::get('/chat-groups', [ChatController::class, 'getChatGroups']);
        Route::post('/chat-groups', [ChatController::class, 'createChatGroup']);
        Route::get('/chat-groups/{id}', [ChatController::class, 'getChatGroup']);
        Route::get('/chat-groups/{id}/messages', [ChatController::class, 'getGroupMessages']);
        Route::get('/chat-groups/{id}/new-messages', [ChatController::class, 'getNewGroupMessages']);
        Route::post('/chat-groups/{id}/messages', [ChatController::class, 'sendGroupMessage']);
    });
});
