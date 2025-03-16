<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealChangeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'user_id',
        'user_name',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
