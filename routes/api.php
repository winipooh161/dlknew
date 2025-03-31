<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Middleware\CheckChatAccess;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Маршруты для чата с защитой аутентификации 
// Проверка аутентификации через web (cookie-сессии)
Route::middleware(['auth:web', CheckChatAccess::class])->group(function () {
    // Базовые маршруты чата
    Route::get('/contacts', [ChatController::class, 'getContacts']);
    Route::get('/chats/{id}/messages', [ChatController::class, 'getMessages']);
    Route::get('/chats/{id}/new-messages', [ChatController::class, 'getNewMessages']);
    Route::post('/chats/{id}/messages', [ChatController::class, 'sendMessage']);
    Route::get('/unread-count', [ChatController::class, 'getUnreadCount']);
    
    // Маршруты для групповых чатов
    Route::get('/chat-groups', [ChatController::class, 'getChatGroups']);
    Route::post('/chat-groups', [ChatController::class, 'createChatGroup']);
    Route::get('/chat-groups/{id}', [ChatController::class, 'getChatGroup']);
    Route::put('/chat-groups/{id}', [ChatController::class, 'updateChatGroup']);
    Route::delete('/chat-groups/{id}', [ChatController::class, 'deleteChatGroup']);
    Route::get('/chat-groups/{id}/messages', [ChatController::class, 'getGroupMessages']);
    Route::get('/chat-groups/{id}/new-messages', [ChatController::class, 'getNewGroupMessages']);
    Route::post('/chat-groups/{id}/messages', [ChatController::class, 'sendGroupMessage']);
    Route::post('/chat-groups/{id}/users', [ChatController::class, 'addChatGroupUser']);
    Route::delete('/chat-groups/{id}/users/{user_id}', [ChatController::class, 'removeChatGroupUser']);
    
    // Проверка новых сообщений
    Route::get('/chats/check-new-messages', [ChatController::class, 'checkNewMessagesInChats']);
    Route::get('/chat-groups/check-new-messages', [ChatController::class, 'checkNewMessagesInGroups']);
    
    // Поиск сообщений
    Route::get('/messages/search', [ChatController::class, 'searchMessages']);
});
