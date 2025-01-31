<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $fillable = ['name', 'active'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'rol_user', 'rol_id', 'user_id');
    }
}
