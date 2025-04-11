<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Models\Sesiones;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    function login(LoginRequest $request)
    {
        try {
            $data_va = $request->validated();
            $credentials = [
                "email" => $data_va['email'],
                "password" => $data_va['password']
            ];
            $token = auth()->attempt($credentials);

            if ($token) {
                $user = auth()->user();
                if (!$user->active){
                    return $this->response("Usuario no activo", 403, true);
                }
    
                if($this->SessionIsActive($user->id)){
                    return $this->response("Tienes una sesion iniciada en otro dispositivo", 405, true, $user->id);
                }

                $role = $user->roles()->first();
                if(!$role){
                    return $this->response("No tienes acceso a tu cuenta", 403, true);
                }
    
                $data =[
                    "user_id"=> $user->id,
                    "direccion" => $data_va['direccion']
                ];
    
                $guardarSession = $this->SessionStart($data);
    
                if($guardarSession["error"]){
                    return $this->response("Error al iniciar session, intentalo mas tarde", 500, true);
                }
    
                return $this->token($token, auth()->user());
            } else {
                return $this->response("Credenciales no válidas", 401, true);
            }
        } catch (\Exception $e) {
            Log::error("Error al iniciar sesion login: ". $e->getMessage());
            return $this->response("Error al iniciar Sesion: ", 500, true);
        }
    
    }

    function loginAdmin(LoginRequest $request)
    {
        try {
            $credentials = $request->validated();
            $ttl = config('jwt.ttl.api', 180);
            auth()->setTTL($ttl);
        
            $token = auth()->attempt($credentials);
        } catch (\Exception $e) {
            return $this->response("Error al iniciar Sesion: ", 500, true);
        }
        if ($token) {
            $user = auth()->user();

            if (!$user->roles[0]->name === "administrador" || !$user->roles[0]->name === "creador"){
                return $this->response("No eres un Dj o Administrador", 403, true, $user->roles[0]->name);
            }

            if (!$user->active){
                return $this->response("Usuario no activo", 403, true);
            }
            return $this->tokenAdmin($token, auth()->user());
        } else {
            return $this->response("Credenciales no válidas", 401, true);
        }
    }

    public function SessionStart($data)
    {
        try {

            $sesion = new Sesiones();
            $sesion->date_login = now();
            $sesion->addressip = $data['direccion'] ?? request()->ip();
            $sesion->user_id = $data['user_id'];
            $sesion->active = true;
            $sesion->save();
    
            return [
                'message' => 'Sesión iniciada correctamente',
                'session' => $sesion,
                'error' => false
            ];
    
        } catch (\Exception $e) {
            Log::error("Error al guardar la session:". $e->getMessage());
            return [
                'message' => 'Error al iniciar la sesión',
                'session' => "",
                "error" => ""
            ];
        }
    }

    public function SessionIsActive($user_id)
    {
        return Sesiones::where('user_id', $user_id)
                    ->where('active', true)
                    ->exists();
    }

    public function DesactivarSessiones($id){
        try {
            // Desactivar cualquier sesión activa anterior del usuario y registrar la fecha de cierre
            Sesiones::where('user_id', $id)
                ->where('active', true)
                ->update([
                    'active' => false,
                    'date_signup' => now()
                ]);


            return $this->response("Sesion correctamente eliminadas",200,false);
        } catch (\Exception $e) {
            Log::error("Error al querer cerrar las sesiones:". $e->getMessage());
            return $this->response("Error al querer cerrar las sesiones",500,false);
        }
    }


    public function tokenAdmin($token, $user){
        $data = [
            'user' =>[
                'name' => $user->name ?? "",
                'email' => $user->email,
                'roles' => $user->roles[0]->name,
            ],
            'access_token' => $token,
            'type' => 'bearer',
        ];

        return $this->response("Sesion correctamente iniciada",200,false, $data);
    }

    public function token($token, $user){
        $data = [
            'user' =>[
                'name' => $user->name ?? "",
                'perfil' => $user->perfil ?? "",
                'plan' => [
                    "name" => $user->perfilplan->plan->name ?? "",
                    "date_start" => $user->perfilplan->date_start ?? "",
                    "date_end" => $user->perfilplan->date_end ?? "",
                    "videos" => $user->perfilplan?->plan_count?->first()?->n_videos ?? "",
                    "audios" => $user->perfilplan?->plan_count?->first()?->n_audios ?? "",
                ],
                'email' => $user->email,
                'roles' => $user->roles[0]->name,
            ],
            'access_token' => $token,
            'type' => 'bearer',
        ];

        // $cookie = cookie('auth_token', $token, 60, '/', null, true, true);
        // return response()->json([
        //     'message' => 'Sesion correctamente iniciada',
        //     'data' => $data,
        // ])->withCookie($cookie)->setStatusCode(200);

        return $this->response("Sesion correctamente iniciada",200,false, $data);
    }

    public function logout(Request $request)
    {
        try {
            $user = auth()->user();
            $this->DesactivarSessiones($user->id);
            auth()->logout();
            return $this->response("Sesion cerrada correctamente", 200, false);
        } catch (\Exception $e) {
            Log::error("Error al cerrar la sesion: ". $e->getMessage());
            return $this->response("Error al cerrar la sesion", 500, true);
        }
    }

    public function tokenResetPassword(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email']);

            $user = User::where('email', $request->email)->first();
            $rol = $user->roles()->first();
            
            if ($rol->name === "administrador" || $rol->name === "creador") {
                return $this->response("Este correo no está asociado con un usuario autorizado", 403, true);
            }

            $status = Password::sendResetLink(
                $request->only('email')
            );


            if ($status === Password::RESET_LINK_SENT) {
                return $this->response("Enlace enviado al correo", 200, false);
            } elseif ($status === Password::INVALID_USER) {
                return $this->response("Este correo no está registrado", 404, true);
            }

        } catch (\Exception $e) {
            Log::error("Error al actualizar la contraseña: ". $e->getMessage());
            return $this->response("Error al actualizar la contraseña", 500, true);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $data = $request->validated();

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->save();
                }
            );

            if($status == Password::INVALID_TOKEN){
                return $this->response("Token invalido", 401, true);
            }
            if($status == Password::INVALID_USER){
                return $this->response("Usuario invalido", 401, true);
            }
            if($status == Password::RESET_THROTTLED){
                return $this->response("Demasiados intentos de restablecimiento de contraseña. Inténtalo de nuevo más tarde.", 429, true);
            }
            if($status == Password::PASSWORD_RESET){
                return $this->response("Contraseña actualizada correctamente", 200, false);
            }        
        } catch (\Exception $e) {
            Log::error("Error al actualizar la contraseña: ". $e->getMessage());
            return $this->response("Error al actualizar la contraseña", 500, true);
        }
    }
}
