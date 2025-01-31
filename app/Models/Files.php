<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
    use HasFactory;

    protected $table  = 'files';

    protected $fillable = [
        'name',
        'bpm',
        'version',
        'type',
        'path',
        'path_preview',
        'n_downloads',
        'active',
        'slider_new',
        'artists_id',
        'genres_id',
        'user_id'
    ];

    public function artist()
    {
        return $this->belongsTo(Artistas::class, 'artists_id');
    }

    public function genre()
    {
        return $this->belongsTo(Generos::class, 'genres_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
