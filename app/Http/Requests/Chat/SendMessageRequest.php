<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SendMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message' => 'nullable|string|max:5000',
            'attachments.*' => 'nullable|file|max:10240', // 10MB максимальный размер файла
            'attachments' => 'nullable|array|max:5', // Максимум 5 файлов
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
            'message.max' => 'Сообщение не должно превышать 5000 символов',
            'attachments.*.max' => 'Размер файла не должен превышать 10MB',
            'attachments.max' => 'Вы можете загрузить не более 5 файлов',
        ];
    }
}
