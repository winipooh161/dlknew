<?php
namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealFeedController extends Controller
{
    public function store(Request $request, Deal $deal)
    {
        if (!in_array(Auth::user()->status, ['coordinator', 'admin'])) {
            return response()->json(['error' => 'Доступ запрещён'], 403);
        }
    
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);
    
        $feed = DealFeed::create([
            'deal_id' => $deal->id,
            'user_id' => Auth::id(),
            'content' => $validated['content'],
        ]);
    
        return response()->json([
            'user_name' => Auth::user()->name,
            'avatar_url' => Auth::user()->avatar_url,
            'content' => $feed->content,
            'date' => $feed->created_at->format('d.m.Y H:i'),
        ]);
    }
}
