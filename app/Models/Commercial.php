<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Commercial Model



    class Commercial extends Model
    {
        use HasFactory;
    
        protected $fillable = [
            'id', 'title', 'article', 'description', 'current_page', 'status', 'price', 'user_id', 'zones', 'total_area', 'projected_area', 'deal_id',
        ];
    
        protected $casts = [
            'zones' => 'array',
        ];
    
        // Связь с сделками
        public function deals()
        {
            return $this->hasMany(Deal::class, 'commercial_id');
        }
    
        // Связь с координатором
        public function coordinator()
        {
            return $this->belongsTo(User::class, 'coordinator_id');
        }
    
        // Связь с автором
        public function user()
        {
            return $this->belongsTo(User::class, 'user_id');
        }
    }
    