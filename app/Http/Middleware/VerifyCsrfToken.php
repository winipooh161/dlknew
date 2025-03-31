<?php
namespace App\Http\Middleware;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Исключаем API-маршруты чата из CSRF-проверки
        'api/chats/*',
        'api/chat-groups/*',
        // Другие исключения если они есть
    ];

    /**
     * Определяет действие при неудачной проверке CSRF-токена для AJAX-запросов
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function handleFailure($request, $exception)
    {
        // Если это AJAX-запрос, возвращаем JSON с ошибкой
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'CSRF-токен недействителен. Пожалуйста, обновите страницу и попробуйте снова.'
            ], 419);
        }
        
        // Иначе используем стандартное поведение
        return parent::handleFailure($request, $exception);
    }
}
