<?php
namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\MessageResource;
use App\Models\User;

class SupportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(Request $request)
    {
        $user = Auth::user();
        $title_site = "Поддержка | Личный кабинет Экспресс-дизайн";
        
        // Жестко задаем ID пользователя поддержки
        $supportUserId = 55;
        
        // Получаем пользователя поддержки
        $supportUser = User::find($supportUserId);
        
        // Проверяем существование пользователя поддержки
        if (!$supportUser) {
            Log::error('Пользователь поддержки с ID 55 не найден');
            return view('module.support', [
                'title_site' => $title_site,
                'supportError' => 'Сервис поддержки временно недоступен'
            ]);
        }
        
        // Подсчитываем количество непрочитанных сообщений
      
        
        return view('support', compact('title_site', 'user', 'supportChat'));
    }
    
    // Другие методы поддержки (например, создание тикета) можно добавить здесь

    public function getNewMessages(Request $request, $id)
    {
        $validated = $request->validate([
            'last_message_id' => 'nullable|integer',
        ]);

        $currentUserId = Auth::id();
        $lastMessageId = $validated['last_message_id'] ?? 0;

        try {
         
       

        } catch (\Exception $e) {
            Log::error('Ошибка при получении новых сообщений: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Ошибка получения сообщений.'], 500);
        }

        return response()->json([
            'current_user_id' => $currentUserId,
          
        ], 200);
    }

    public function markMessagesAsRead(Request $request, $id)
    {
        $currentUserId = Auth::id();

        try {
       
        } catch (\Exception $e) {
            Log::error('Ошибка при пометке сообщений как прочитанных: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Ошибка пометки сообщений.'], 500);
        }

        return response()->json(['success' => true], 200);
    }

    public function sendMessage(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string|max:1000',
            ]);

       

            return response()->json([
                'success' => true,
             
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка отправки сообщения: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Не удалось отправить сообщение. Пожалуйста, попробуйте позже.'
            ], 500);
        }
    }
}
