<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AutenticationJWT
{
    public function handle($request, Closure $next)
    {
        try {
            // Verifica si el token JWT está presente en la solicitud
            if (!Auth::check()) {
                return response()->json([
                    'autorization' => true,
                    'error' => true,
                    'message' => 'Acceso no autorizado'
                ], 401);
            }

            // Obtiene el usuario autenticado
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }

            // Obtiene el rol del usuario
            $rol = $user->roles()->first();
            if (!$rol) {
                return response()->json(['error' => 'Rol no encontrado'], 403);
            }

            // Agrega el rol al objeto de solicitud
            $request->attributes->add(['rol' => $rol->nombre]);

            return $next($request);
        } catch (JWTException $e) {
            // Maneja excepciones específicas de JWT
            return response()->json(['error' => 'Token inválido o expirado'], 401);
        } catch (\Exception $e) {
            // Maneja otras excepciones
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}
