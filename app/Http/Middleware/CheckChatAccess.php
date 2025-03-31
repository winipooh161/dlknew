<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckChatAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !in_array($user->status, ['admin', 'partner', 'coordinator', 'architect', 'designer', 'visualizer', 'support'])) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }
        return $next($request);
    }
}
