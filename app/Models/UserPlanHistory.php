<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPlanHistory extends Model
{
    use HasFactory;

    protected $table = "user_plan_history";

    protected $fillable = [
        "date_shop",
        "amount",
        "plan_id",
        "user_id",
    ];
}
