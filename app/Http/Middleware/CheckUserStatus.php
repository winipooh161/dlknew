<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $status
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$statuses)
    {
        if (Auth::check()) {
            // Пользователь с статусом "admin" получает доступ ко всем страницам
            if (Auth::user()->status === 'admin') {
                return $next($request);
            }

            // Проверяем, соответствует ли статус пользователя разрешённым
            if (in_array(Auth::user()->status, $statuses)) {
                return $next($request);
            }
        }

        return redirect()->route('home')->with('error', 'У вас недостаточно прав!');
    }
}
