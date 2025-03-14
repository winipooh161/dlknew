<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Common extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'title', 'article', 'description', 'status', 'price', 'current_page', 'user_id', 'deal_id',
    ];

    // Связь с координатором
    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    // Связь с сделками
    public function deals()
    {
        return $this->hasMany(Deal::class, 'common_id');
    }
}

