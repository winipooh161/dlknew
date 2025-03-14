<?php
namespace App\Services;
use SMSRU\Api;
class SmsService
{
    protected $smsru;
    public function __construct()
    {
        $this->smsru = new Api('6CDCE0B0-6091-278C-5145-360657FF0F9B'); // Ваш уникальный API-ключ
    }
    // Генерация и отправка кода
    public function sendCode($phone)
    {
        // Генерация случайного 6-значного кода
        $code = rand(100000, 999999);
        // Создаем объект данных для отправки
        $data = new \stdClass();
        $data->to = $phone;
        $data->text = "Ваш код для входа: $code"; // Сообщение с кодом
        $data->translit = 1; // Перевод русских символов в латиницу для экономии символов
        $data->test = 0; // Отключаем тестовый режим
        // Отправка сообщения
        $sms = $this->smsru->send_one($data);
        // Проверка результата
        if ($sms->status == 'OK') {
            // Сохранение кода в базе данных
            return $code;
        } else {
            throw new \Exception("Ошибка при отправке сообщения: " . $sms->status_text);
        }
    }
}
