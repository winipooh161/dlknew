<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
            'message' => [
                'nullable',
                'string',
                'max:5000',
                function ($attribute, $value, $fail) {
                    if (empty($value) && empty($this->file('attachments'))) {
                        $fail('Необходимо ввести текст сообщения или прикрепить файл.');
                    }
                },
            ],
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx|max:2048', // проверка типов файлов
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
            'attachments.*.max' => 'Размер файла не должен превышать 2MB',
            'attachments.max' => 'Вы можете загрузить не более 5 файлов',
        ];
    }
}
