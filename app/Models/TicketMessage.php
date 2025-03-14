<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    protected $fillable = ['ticket_id', 'user_id', 'message']; // Убедитесь, что 'ticket_id'

    // Связь с тикетом
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id'); // Указываем 'ticket_id' как внешний ключ
    }

    // Связь с пользователем
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
