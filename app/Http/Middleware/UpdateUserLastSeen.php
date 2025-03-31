<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateUserLastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Обновляем last_seen_at только если прошло не менее 1 минуты с последнего обновления
            // или если last_seen_at пусто
            if (!$user->last_seen_at || now()->diffInMinutes($user->last_seen_at) >= 1) {
                $user->last_seen_at = now();
                $user->save();
            }
        }
        
        return $next($request);
    }
}
