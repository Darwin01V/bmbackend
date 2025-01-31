<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artistas extends Model
{
    use HasFactory;

    protected $table = "artists";

    protected $fillable = [
        'name',
        'active',
    ];

    public function files()
    {
        return $this->hasMany(Files::class, 'artists_id');
    }
}
