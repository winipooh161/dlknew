<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        // Старые поля:
        'client_name',
        'name',
        'client_phone',
        'total_sum',
        'status',
        'link',
        'registration_token',
        'registration_token_expiry',
        'user_id',
        'coordinator_id',
        'avatar_path',
        'client_city',
        'client_email',
        'object_type',
        'package',
        'has_animals',
        'has_plants',
        'object_style',
        'measurements',
        'rooms_count',
        'deal_end_date',
        'common_id',
        'commercial_id',

        // Новые поля (Общие)
        'completion_responsible',
        'office_equipment',
        'coordinator_score',
        'measuring_cost',
        'project_budget',
        'created_date',
        'client_info',
        'payment_date',
        'execution_comment',
        'comment',

        // Поля блока "ЗАКАЗ"
        'project_number',
        // 'order_stage', // удалено, т.к. статус хранится только в status
        'price_service',
        'rooms_count_pricing',
        'execution_order_comment',
        'execution_order_file',
        'client_timezone',
        'office_partner_id',
        'client_account_link',
        'chat_link',

        // Поля блока "РАБОТА НАД ПРОЕКТОМ"
        'measurement_comments',
        'measurements_file',
        'brief',
        'start_date',
        'project_duration',
        'project_end_date',
        'architect_id',
        'final_floorplan',
        'designer_id',
        'final_collage',
        'visualizer_id',
        'visualization_link',
        'final_project_file',

        // Поля блока "ФИНАЛ ПРОЕКТА"
        'work_act',
        'client_project_rating',
        'architect_rating_client',
        'architect_rating_partner',
        'architect_rating_coordinator',
        'designer_rating_client',
        'designer_rating_partner',
        'designer_rating_coordinator',
        'visualizer_rating_client',
        'visualizer_rating_partner',
        'visualizer_rating_coordinator',
        'coordinator_rating_client',
        'coordinator_rating_partner',
        'chat_screenshot',
        'coordinator_comment',
        'archicad_file',

        // Поля блока "О СДЕЛКЕ"
        'contract_number',
        'contract_attachment',
        'deal_note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chat()
    {
        return $this->hasOne(Chat::class);
    }

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function commercial()
    {
        return $this->belongsTo(Commercial::class, 'commercial_id');
    }

    public function brief()
    {
        return $this->belongsTo(Common::class, 'common_id');
    }

    public function briefs()
    {
        return $this->hasMany(Common::class, 'deal_id');
    }

    public function commercials()
    {
        return $this->hasMany(Commercial::class, 'deal_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'deal_user', 'deal_id', 'user_id');
    }

    public function coordinators()
    {
        return $this->users()->wherePivot('role', 'coordinator');
    }

    public function responsibles()
    {
        return $this->users()->wherePivot('role', 'responsible');
    }

    public function allUsers()
    {
        return $this->users()->wherePivotIn('role', ['responsible', 'coordinator']);
    }

    public function getUnreadMessagesCount($userId)
    {
        return $this->chatMessages()
            ->where('user_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }

    public function dealFeeds()
    {
        return $this->hasMany(DealFeed::class);
    }

    public function changeLogs()
    {
        return $this->hasMany(DealChangeLog::class);
    }

    public function view(User $user, Deal $deal)
    {
        return $deal->users->contains($user->id);
    }
}
