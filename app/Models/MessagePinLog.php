<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessagePinLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'message_id',
        'chat_id',
        'action', // 'pin' или 'unpin'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
