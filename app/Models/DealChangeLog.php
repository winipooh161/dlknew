<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealChangeLog extends Model
{
    protected $fillable = [
        'deal_id',
        'user_id',
        'user_name',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];
}
