<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    function login(LoginRequest $request)
    {
        try {
            $credentials = $request->validated();
            $token = auth()->attempt($credentials);
        } catch (\Exception $e) {
            return $this->response("Error al iniciar Sesion: " . $e->getMessage(), 500, true);
        }
        if ($token) {
            $user = auth()->user();
            if (!$user->active){
                return $this->response("Usuario no activo", 403, true);
            }
            return $this->token($token, auth()->user());
        } else {
            return $this->response("Credenciales no válidas", 401, true);
        }
    }

    public function token($token, $user){
        $data = [
            'user' => $user,
            'access_token' => $token,
            'type' => 'bearer',
        ];

        return $this->response("Sesion correctamente iniciada",200,false, $data);
    }
}
