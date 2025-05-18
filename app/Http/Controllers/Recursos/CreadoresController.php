<?php

namespace App\Http\Controllers\Recursos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Recursos\CreateCreadorRequest;
use App\Http\Requests\Recursos\UpdateComisionCreadorRequest;
use App\Models\Files;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CreadoresController extends Controller
{
    protected $user;

    public function __construct(User $user){
        $this->user = $user;
    }

    public function getCreadores(){
        try {
            $data = $this->user->with(['perfilCreador'])
            ->whereHas('roles', function ($query) {
                $query->where('name', 'creador');
            })->with('MontoCreador')
            ->paginate(10);
            
            // Transformar los datos para verificar la fecha de vencimiento
            $data->getCollection()->transform(function ($user) {
                // Comprobar si el usuario tiene un registro de MontoCreador
                if (isset($user->MontoCreador) && $user->MontoCreador !== null) {
                    
                    // Convertir la fecha_fin a un objeto Carbon para comparación
                    $fechaFin = Carbon::parse($user->MontoCreador->fecha_fin);
                    $ahora = Carbon::now();
                    
                    // Si la fecha de fin es menor que ahora (ha expirado)
                    if ($fechaFin < $ahora) {
                        // Modificar directamente el valor monto_mes
                        $user->MontoCreador->monto_mes = "0";
                    }
                }
                return $user;
            });
    
            return $this->response("Datos obtenidos", 200, false, $data);
        } catch (\Throwable $th) {
            Log::error("Error en getCreadores: " . $th->getMessage());
            return $this->response("Ocurrio un error al obtener los datos", 200, false);
        }
    }

    public function getCreador(int $id){
        try {
            $data = $this->user->find($id);

            return $this->response("Datos obtenidos", 200, false, $data);
        } catch (\Throwable $th) {
            return $this->response("Ocurrio un error al obtener los datos", 500, false);
        }
    }

    public function createCreador(CreateCreadorRequest $request){
        try {
            DB::beginTransaction();
    
            $data = $request->validated();
    
            $user = User::create([
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'root' => false,
                'active' =>false,
            ]);
    
            $user->roles()->attach(3);
    
            $user->perfilCreador()->create([
                'first_name'=> $data['first_name'],
                'last_name'=> $data['last_name'],
                'country'=> $data['country'],
                'experience'=> $data['experience'],
                'working'=>$data['working'],
                'details'=>$data['details'],
                'comision' => 0.5
            ]);
    
            // Enviar notificación al administrador
            $adminEmail = config('mail.admin_email'); // Debes configurar esto en tu archivo de configuración
            $admin = new \stdClass();
            $admin->email = $adminEmail;
            
            // Si quieres enviar la notificación a una dirección de correo específica
            Notification::route('mail', $adminEmail)
                ->notify(new \App\Notifications\NewCreadorNotification($user->load('perfilCreador')));
    
            DB::commit();
    
            $data = $user->load('perfilCreador', 'roles');
            return $this->response("Usuario creado con exito", 200, false, $data);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("ERROR AL CREAR". $e->getMessage());
            return $this->response("Error en el servicio" . $e->getMessage(), 500, false);
        }
    }

    public function updateStatus(int $id){
        try {
            $data = $this->user->find($id);

            $data->update([
                "active" => !$data->active
            ]);

            $status = $data->active ? "Aprobado" : "Inactivado";
            return $this->response("Creador {$status} correctamente", 200, false);
        } catch (\Throwable $th) {
            return $this->response("Ocurrio un error al obtener los datos", 500, false);
        }
    }

    public function updateComision(int $id, UpdateComisionCreadorRequest $request){
        try {
            $validate_data = $request->validated();
            $data = $this->user->find($id);

            $data->perfilCreador->update([
                'comision' => $validate_data['comision']
            ]);


            return $this->response("Comision actualizada correctamente", 200, false);
        } catch (\Throwable $th) {
            return $this->response("Ocurrio un error al obtener los datos", 500, false);
        }
    }
}
