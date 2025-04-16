<?php

use App\Http\Controllers\Administacion\DashboardController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\Recursos\ArtistasController;
use App\Http\Controllers\Recursos\CreadoresController;
use App\Http\Controllers\Recursos\FilesController;
use App\Http\Controllers\Recursos\GenerosController;
use App\Http\Controllers\Seguridad\AuthController;
use App\Http\Controllers\Seguridad\UsuarioController;
use App\Http\Controllers\Ventas\ClientesController;
use App\Http\Controllers\Ventas\PagosController;
use App\Http\Controllers\Ventas\PlanesController;
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
    // api/v1/admin/-----

        Route::group(['prefix' => 'admin'], function () {
            Route::post('security/login', [AuthController::class, 'loginAdmin']);
            Route::get('security/admin-verifique', [UsuarioController::class, 'adminExists']);
            Route::post('security/admin-create', [UsuarioController::class, 'createUserAdmin']);

            Route::middleware(['ckeck.jwt'])->group(function () {
                // Seguridades Login, Usuarios, Roles, Sessiones
                Route::group(['prefix' => 'security'], function () {
                    Route::get('/users', [UsuarioController::class, 'getUsers']);
                    Route::post('/user', [UsuarioController::class, 'createUsers']);
                    Route::put('/user/{id}', [UsuarioController::class, 'updateUser']);
                    Route::patch('/user/{id}', [UsuarioController::class, 'updatePassword']);
                    Route::delete('/user/{id}', [UsuarioController::class, 'toggleUserStatus']);

                    Route::get('/roles', [UsuarioController::class, 'getRoles']);
                });

                Route::group(['prefix' => 'recursos'], function () {
                    Route::get('/artistas', [ArtistasController::class, 'getArtistas']);
                    Route::post('/artista', [ArtistasController::class, 'createArtista']);
                    Route::put('/artista/{id}', [ArtistasController::class, 'updateArtista']);
                    Route::delete('/artista/{id}', [ArtistasController::class, 'toggleArtistaStatus']);

                    Route::get('/generos', [GenerosController::class, 'getGeneros']);
                    Route::get('/genero/{id}', [GenerosController::class, 'getGenero']);
                    Route::post('/genero', [GenerosController::class, 'createGenero']);
                    Route::put('/genero/{id}', [GenerosController::class, 'updateGenero']);
                    Route::delete('/genero/{id}', [GenerosController::class, 'toggleGeneroStatus']);

                    
                    Route::get('/files', [FilesController::class, 'getFiles']);
                    Route::get('/file/{id}', [FilesController::class, 'getFile']);
                    Route::post('/file', [FilesController::class, 'createFiles']);
                    Route::put('/file/status', [FilesController::class, 'StatusFile']);
                    Route::put('/file/{id}', [FilesController::class, 'updateFile']);
                    Route::put('/file/toggle/{id}', [FilesController::class, 'toggleFileStatus']);
                    Route::delete('/file/{id}', [FilesController::class, 'DeleteFile']);


                    Route::get('/creadores', [CreadoresController::class, 'getCreadores']);
                    Route::get('/creador/{id}', [CreadoresController::class, 'getCreador']);
                    Route::post('/creador', [CreadoresController::class, 'createCreador']);
                    Route::patch('/creador/{id}', [CreadoresController::class, 'updateComision']);
                    Route::delete('/creador/{id}', [CreadoresController::class, 'updateStatus']);
                    
                });

                Route::group(['prefix' => 'ventas'], function () {

                    Route::get('/planes', [PlanesController::class, 'getPlanes']);
                    Route::get('/plan/{id}', [PlanesController::class, 'getPlan']);
                    Route::post('/plan', [PlanesController::class, 'createPlan']);
                    Route::put('/plan/{id}', [PlanesController::class, 'updatePlan']);
                    Route::delete('/plan/{id}', [PlanesController::class, 'togglePlanStatus']);
                    
                    Route::get('/clientes', [ClientesController::class, 'getClientes']);
                    Route::put('/cliente/{id}', [ClientesController::class, 'updateCliente']);
                    Route::delete('/cliente/{id}', [ClientesController::class, 'toggleClienteStatus']);
                    
                });

                Route::group(['prefix' => 'dashboard'], function () {
                    Route::get('/dataadministrador', [DashboardController::class, 'getDataAdministrador']);
                    Route::get('/datacreador', [DashboardController::class, 'getDataCreador']);
                });

                Route::group(['prefix' => 'configuracion'], function () {
                    Route::put('/{id}', [ConfigurationController::class, 'updateConfig']);
                    Route::get('/', [ConfigurationController::class, 'getConfiguration']);
                });
            });
        });
    

    
    // Rutas publicas
    // api/v1/public/-----
    Route::group(['prefix' => 'public'], function () {

        Route::post('/security/login', [AuthController::class, 'login']);
        Route::post('/security/create_cliente', [ClientesController::class, 'createCliente']);
        Route::put('security/cerrar_sesiones/{id}', [AuthController::class, 'DesactivarSessiones']);
        Route::post('security/forgot-password', [AuthController::class, 'tokenResetPassword']);
        Route::post('/security/resete-password', [AuthController::class, 'resetPassword']);
        Route::get('/reset-password/{token}', function ($token) {
            $app_front = env('APP_FRONT');
            return redirect("$app_front/autenticacion/restablecer-contrasena/$token");
        })->name('password.reset');

        Route::group(['prefix' => 'recursos'], function () {

            Route::get('/files/audios', [FilesController::class, 'getFilesAudios']);
            Route::get('/files/audios_destact', [FilesController::class, 'getFilesAudiosDesct']);
            Route::get('/files/videos', [FilesController::class, 'getFilesVideo']);
            Route::get('/files/videos_destact', [FilesController::class, 'getFilesVideoDesct']);
            Route::get('/files/slider-new', [FilesController::class, 'getFilesSliderNuevosLanzamientos']);

            Route::get('/artistas', [ArtistasController::class, 'getArtistasActive']);
            Route::get('/generos', [GenerosController::class, 'getGenerosActive']);

            Route::post('/creador', [CreadoresController::class, 'createCreador']);

        });
        
        Route::group(['prefix' => 'ventas'], function () {

            Route::get('/planes', [PlanesController::class, 'getPlanesActive']);
            Route::post('/plan/pago', [PagosController::class, 'pagocreated']);
            Route::post('/plan/pago_confirmed', [PagosController::class, 'webhook']);

        });

        Route::middleware(['ckeck.jwt'])->group(function (){

            Route::group(['prefix' => 'recursos'], function () {
                Route::get('/file/download/{id}', [FilesController::class, 'downlanFile']);
                Route::get('/file/download_verifique/{id}', [FilesController::class, 'verifiquedownload']);
            });

            Route::group(['prefix' => 'clientes'], function () {
                Route::get('/contador', [ClientesController::class, 'contadorDescargas']);
                Route::put('/usuario', [ClientesController::class, 'updateCliente']);
                Route::put('/cliente-profile', [ClientesController::class, 'updateClientePerfil']);
            });

        });

        Route::group(['prefix' => 'configuracion'], function () {

            Route::get('/', [ConfigurationController::class, 'getConfiguration']);

        });

        
    });
});



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
