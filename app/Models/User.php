<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\CanResetPassword; // <-- Esta es una interfaz
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait; // <-- Este es el trait que debes usar
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, CanResetPassword
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPasswordTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'verifique_email',
        'password',
        'active',
        'root',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    // Relaciones

    public function perfilplan()
    {
        return $this->hasOne(PlanPerfil::class);
    }

    public function perfil()
    {
        return $this->hasOne(Perfil::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Roles::class, 'rol_user', 'user_id', 'rol_id');
    }

    public function sesiones(){
        return $this->hasMany(Sesiones::class);
    }

    public function files()
    {
        return $this->hasMany(Files::class, 'user_id');
    }

    public function ventas(){
        return $this->hasMany(Ventas::class, 'user_id');
    }

    public function perfilCreador()
    {
        return $this->hasOne(PerfilCreador::class, 'user_id');
    }

    
}
