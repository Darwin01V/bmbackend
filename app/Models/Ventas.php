<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ventas extends Model
{
    use HasFactory;

    protected $table = "sales";

    protected $fillable = [
        'date_shop',
        'amount',
        'plan_id',
        'user_id',
        'estado'
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function plan(){
        return $this->belongsTo(Planes::class);
    }
}
