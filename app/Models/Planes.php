<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planes extends Model
{
    use HasFactory;

    protected $table = 'plans';

    protected $fillable = [
        'name',
        'description',
        'time',
        'type',
        'price',
        'discount_percentage',
        'unlimited',
        'n_audios',
        'n_videos',
        'active'
    ];

    
}
