<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'error' => true,
                'message' => 'El recurso que estás buscando no está disponible.'
            ], 404);
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return response()->json([
                'error' => true,
                'message' => 'La URL solicitada no corresponde a ningún recurso.'
            ], 404);
        }

        if ($exception instanceof TokenExpiredException) {
            Log::warning('Token expirado: ' . $exception->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Token expirado, vuelve a iniciar sesión.'
            ], 401);
        } elseif ($exception instanceof TokenInvalidException) {
            Log::warning('Token inválido: ' . $exception->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Token inválido, vuelve a iniciar sesión.'
            ], 401);
        } elseif ($exception instanceof JWTException) {
            Log::warning('Excepción JWT: ' . $exception->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Token inválido, vuelve a iniciar sesión.'
            ], 401);
        }

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return response()->json([
                'error' => true,
                'message' => $exception->errors(),
                'code' => 422
            ], 422);
        }

        return parent::render($request, $exception);
    }
}
