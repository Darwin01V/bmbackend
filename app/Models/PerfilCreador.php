<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfilCreador extends Model
{
    use HasFactory;

    protected $table = "perfil_creador";

    protected $fillable = [
        'first_name',
        'last_name',
        'country',
        'experience',
        'working',
        'details',
        'user_id', 
        'comision'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
