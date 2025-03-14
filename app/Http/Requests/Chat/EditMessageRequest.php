<?php

namespace App\Http\Requests\Chat;

use App\Models\Message;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class EditMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $messageId = $this->route('messageId');
        $message = Message::findOrFail($messageId);
        $user = Auth::user();
        
        // Запрещаем редактирование системных уведомлений
        if ($message->message_type === 'notification' || $message->is_system) {
            return false;
        }
        
        // Разрешаем редактирование только автору сообщения или администраторам/координаторам
        return $message->sender_id === Auth::id() || 
               in_array($user->status, ['coordinator', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message' => 'required|string|max:5000',
        ];
    }
    
    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages()
    {
        return [
            'message.required' => 'Текст сообщения обязателен',
            'message.max' => 'Длина сообщения не должна превышать 5000 символов',
        ];
    }
}
