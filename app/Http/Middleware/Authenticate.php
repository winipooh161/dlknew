<?php
namespace App\Http\Middleware;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Для AJAX-запросов не делаем редиректа, а возвращаем 401 статус
        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return null;
        }
        
        return $request->expectsJson() ? null : route('login');
    }
}
