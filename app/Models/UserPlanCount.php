<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPlanCount extends Model
{
    use HasFactory;

    protected $table = "user_plan_count";

    protected $fillable = [
        "user_plan_id",
        "n_audios",
        "n_videos"
    ];

    public function PlanUser()
    {
        return $this->belongsTo(PlanPerfil::class);
    }

}
