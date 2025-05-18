<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MontoCreador extends Model
{
    use HasFactory;

    protected $table = 'monto_creador';

    protected $fillable = [
        'creador_id',
        'n_descargas',
        'monto_mes',
        'fecha_inicio',
        'fecha_fin'
    ];

    public function creador() {
        return $this->belongsTo(User::class, 'creador_id');
    }
}
