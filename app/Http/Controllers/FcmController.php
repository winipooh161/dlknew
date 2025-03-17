<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Контроллер FCM удалён, так как функциональность Firebase отключена.
class FcmController extends Controller
{
    public function updateToken(Request $request)
    {
        return response()->json(['error' => 'Firebase функциональность отключена'], 501);
    }

    public function sendTestNotification(Request $request)
    {
        return response()->json(['error' => 'Firebase функциональность отключена'], 501);
    }
}
