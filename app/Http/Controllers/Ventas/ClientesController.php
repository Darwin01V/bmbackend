<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clientes\CreateClienteRequest;
use App\Http\Requests\Clientes\UpdateClienteRequest;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientesController extends Controller
{
    protected $user;

    public function __construct(User $user){
        $this->user = $user;
    }

    public function getClientes(){
        try {
            $data = $this->user->with(['perfil', 'roles'])
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'cliente');
                })
                ->paginate(10);
    
            // Mapeamos los datos para devolver solo los campos requeridos
            $data->getCollection()->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'email' => $item->email,
                    'active' => $item->active,
                    'name' => $item->perfil->first_name ?? "",
                    'lastname' => $item->perfil->last_name ?? "",
                    'roles' => "Cliente", // Toma el primer rol
                ];
            });
    
            return $this->response("Datos obtenidos", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error en el servicio: " . $e->getMessage(), 200, false);
        }
    } 

    public function createCliente(CreateClienteRequest $request){
        try {
            DB::beginTransaction();

            $data = $request->validated();

            $user = User::create([
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'root' => false,
                'active' =>true,
            ]);

            $user->roles()->attach(2);

            DB::commit();

            $data = $user->load('perfil', 'roles');
            return $this->response("Usuario creado con exito", 200, false, $data);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response("Error en el servicio" . $e->getMessage(), 200, false);
        }
    }

    public function updateCliente(UpdateClienteRequest $request, $id){
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);
            $data = $request->validated();
    
            $user->fill([
                'email' => $data['email'] ?? $user->email,
            ]);
    
            if (!empty($data['password'])) {
                $user->password = bcrypt($data['password']);
            }
    
            $user->save();

            $user->perfil()->updateOrCreate(
                ['user_id' => $user->id], // Condición para encontrar el perfil
                [
                    "first_name" => $data['name'] ?? '',
                    "last_name" => $data['lastname'] ?? '',
                ]
            );
    
            DB::commit();
    
            $user->load('perfil', 'roles');
    
            return $this->response("Usuario actualizado con éxito", 200, false, $user);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response("Error en el servicio: " . $e->getMessage(), 500, true);
        }
    }
    
    public function toggleClienteStatus($id){
        try {
            $data = User::find($id);
            if (!$data) {
                return $this->response("Cliente no encontrado", 404, true);
            }
    
            // Alternar el estado del usuario (si está activo, lo desactiva y viceversa)
            $data->update(['active' => !$data->active]);
    
            $status = $data->active ? "activado" : "desactivado";
            return $this->response("Cliente {$status} correctamente", 200, false);
        } catch (\Exception $e) {
            return $this->response("Error en el servicio: " . $e->getMessage(), 500, true);
        }
    }

    public function contadorDescargas(Request $request)
    {
        try {
            $user = auth()->user();
            $plan = $user->perfilplan?->plan_count?->first(); // o suscripción activa
    
            $data = [
                "restante" => 0
            ];
    
            if (!$plan) {
                return $this->response("No tienes un plan activo", 404, true,$data);
            }

            if($plan->unlimited){
                $data['restante'] = 9999;
                return $this->response("Plan con exito", 200, false,$data);
            }
    
            $data['restante'] = $plan->n_audios + $plan->n_videos;
    
            return $this->response("Plan con exito", 200, false,$data);
        } catch (\Exception $e) {
            Log::error("Error en el servicio contadorDescargas: " . $e->getMessage());
            return $this->response("Error en el servicio", 500, true);
        }
    }

}
