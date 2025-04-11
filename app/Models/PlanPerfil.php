<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanPerfil extends Model
{
    use HasFactory;

    protected $table = 'plan_profile';

    protected $fillable = [
        'user_id',
        'plan_id',
        'active',
        'date_start',
        'date_end',
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function plan()
    {
        return $this->belongsTo(Planes::class);
    }

    public function plan_count()
    {
        return $this->hasMany(UserPlanCount::class, 'user_plan_id');
    }
}
