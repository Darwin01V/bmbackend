<?php

use App\Http\Controllers\Seguridad\AuthController;
use App\Http\Controllers\Seguridad\UsuarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Version 1.0
Route::group(['prefix' => 'v1'], function () {
    
    // Rutas del Administrador
    Route::group(['prefix' => 'admin'], function () {
        // Seguridades Login, Usuarios, Roles, Sessiones
        Route::group(['prefix' => 'security'], function () {
            Route::get('/users', [UsuarioController::class, 'getUsers']);
            Route::post('/user', [UsuarioController::class, 'createUsers']);
            Route::put('/user/{id}', [UsuarioController::class, 'updateUser']);
            Route::put('/user/{id}', [UsuarioController::class, 'updatePassword']);
            Route::put('/user/{id}', [UsuarioController::class, 'toggleUserStatus']);
        });

        // 

    });

    // Rutas publicas
    Route::group(['prefix' => 'public'], function () {
        Route::post('/security/login', [AuthController::class, 'login']);
    });
});



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
