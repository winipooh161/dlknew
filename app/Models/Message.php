<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $sender_id
 * @property int $chat_id
 * @property int|null $receiver_id
 * @property string $message
 * @property bool $is_read
 * @property \DateTime|null $read_at
 * @property bool $is_pinned
 * @property bool $is_system
 * @property string $message_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $sender
 * @property-read string $sender_name
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'chat_id',
        'receiver_id',
        'message',
        'is_read',
        'read_at',
        'is_pinned',
        'is_system',
        'message_type',
        'attachments',
        'file_path',
        'delivered_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'is_pinned' => 'boolean',
        'is_system' => 'boolean',
        'attachments' => 'array',
        'delivered_at' => 'datetime'
    ];

    /**
     * Get the sender of this message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver of this message.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the chat this message belongs to.
     */
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
