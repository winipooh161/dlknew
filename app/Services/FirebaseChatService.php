<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use App\Models\Message;

class FirebaseChatService
{
    protected $database;
    
    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
        $this->database = $factory->createDatabase();
    }
    
    public function storeMessage(Message $message)
    {
        $data = [
            'id'         => $message->id,
            'sender_id'  => $message->sender_id,
            'chat_id'    => $message->chat_id,
            'message'    => $message->message,
            'created_at' => $message->created_at->toDateTimeString(),
        ];
        
        $this->database
            ->getReference('messages/' . $message->chat_id . '/' . $message->id)
            ->set($data);
    }
}
