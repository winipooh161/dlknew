<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $status
 * @property string|null $city
 * @property string|null $phone
 * @property string|null $contract_number
 * @property string|null $comment
 * @property string|null $portfolio_link
 * @property int|null $experience
 * @property float|null $rating
 * @property int|null $active_projects_count
 * @property string|null $firebase_token
 * @property string|null $verification_code
 * @property \DateTime|null $verification_code_expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'city',
        'phone',
        'contract_number',
        'comment',
        'portfolio_link',
        'experience',
        'rating',
        'active_projects_count',
        'firebase_token',
        'verification_code',
        'verification_code_expires_at',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'string',
        'verification_code_expires_at' => 'datetime',
    ];

    public function deals()
    {
        return $this->hasMany(Deal::class, 'user_id');
    }

    public function chats()
    {
        return $this->belongsToMany(Chat::class, 'chat_user');
    }

    public function responsibleDeals()
    {
        return $this->belongsToMany(Deal::class, 'deal_responsible', 'user_id', 'deal_id');
    }

    public function getAvatarUrlAttribute()
    {
        return !empty($this->attributes['avatar_url']) 
            ? asset('' . ltrim($this->attributes['avatar_url'], '')) 
            : asset('storage/group_default.svg');
    }
    
    public function isCoordinator()
    {
        return $this->status === 'coordinator';
    }

    public function coordinatorDeals()
    {
        return $this->belongsToMany(Deal::class, 'deal_user')
                    ->withPivot('role')
                    ->wherePivot('role', 'coordinator');
    }

    public function tokens()
    {
        return $this->hasMany(UserToken::class);
    }
}
