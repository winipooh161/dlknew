<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'message' => 'required_without:attachments|string|nullable',
            'attachments' => 'sometimes|array',
            'attachments.*' => 'file|max:10240', // 10MB max
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'message.required_without' => 'Сообщение не может быть пустым, если нет вложений',
            'attachments.*.file' => 'Вложение должно быть файлом',
            'attachments.*.max' => 'Размер файла не должен превышать 10MB',
        ];
    }
}
