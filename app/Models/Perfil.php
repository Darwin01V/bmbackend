<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    use HasFactory;

    protected $table = 'profile';

    protected $fillable = [
        'path_profile', 
        'first_name', 
        'last_name', 
        'user_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
