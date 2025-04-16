<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CreateAdminUserRequest;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Perfil;
use App\Models\Rol;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UsuarioController extends Controller
{   
    protected $user;

    public function __construct(User $user){
        $this->user = $user;
    }

    public function getUsers(){
        try {
            $user = auth()->user();
            $data = $this->user->with(['roles'])->whereHas('roles', function ($query) {
                $query->where('name', 'administrador');
            })
            ->where('id', '!=', $user->id)
            ->where('root', 0)
            ->paginate(10);
            return $this->response("Datos obtendios", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error en el servicio" . $e->getMessage(), 200, false);
        }
    }

    public function getRoles(){
        try {
            $data = Rol::where('active',1)->get();
            return $this->response("Datos obtendios", 200, false, $data);
        } catch (\Throwable $th) {
            return $this->response("Error en el servicio", 200, false);
        }
    }

    public function getUser($id){
        try {
            $data = $this->user->find($id)->with(['perfil','roles'])->get();
            return $this->response("Datos obtendios", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error en el servicio" . $e->getMessage(), 200, false);
        }
    }

    public function getUserSessions($id){
        try {
            $data = $this->user->with(['perfil','roles','sesiones'])->find($id);
            return $this->response("Datos obtendios", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error en el servicio" . $e->getMessage(), 200, false);
        }
    }

    public function createUsers(CreateUserRequest $request){
        try {
            DB::beginTransaction();

            $data = $request->validated();

            $user = User::create([
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'root' => false,
                'active' => true,
            ]);

            // $perfil = new Perfil([
            //     'path_profile' => "",
            //     'first_name' => $data['perfil']['first_name'],
            //     'last_name' => $data['perfil']['last_name'],
            // ]);

            // $user->perfil()->save($perfil);

            $user->roles()->attach(1);

            DB::commit();

            $data = $user->load('roles');
            return $this->response("Usuario creado con exito", 200, false, $data);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response("Error en el servicio" . $e->getMessage(), 500, false);
        }
    }

    public function updateUser(UpdateUserRequest $request, $id){
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Buscar el usuario
            $user = User::find($id);
            if (!$user) {
                return $this->response("Usuario no encontrado", 404, true);
            }

            // Actualizar usuario
            $user->update([
                'email' => $data['email'],
            ]);

            // Si se envía una nueva contraseña, actualizarla
            if (!empty($data['password'])) {
                $user->update(['password' => bcrypt($data['password'])]);
            }

            DB::commit();

            $user->load('perfil', 'roles');

            return $this->response("Usuario actualizado correctamente", 200, false, $user);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response("Error en el servicio: " . $e->getMessage(), 500, true);
        }
    }

    public function updatePassword(UpdatePasswordRequest $request, $id){
        try {
            $user = User::find($id);
            if (!$user) {
                return $this->response("Usuario no encontrado", 404, true);
            }

            // Verificar que la contraseña actual sea correcta
            if (!Hash::check($request->current_password, $user->password)) {
                return $this->response("La contraseña actual es incorrecta", 400, true);
            }

            // Actualizar la contraseña
            $user->update([
                'password' => bcrypt($request->new_password),
            ]);

            return $this->response("Contraseña actualizada correctamente", 200, false);
        } catch (\Exception $e) {
            return $this->response("Error en el servicio: " . $e->getMessage(), 500, true);
        }
    }

    public function toggleUserStatus($id){
        try {
            $user = User::find($id);
            if (!$user) {
                return $this->response("Usuario no encontrado", 404, true);
            }
    
            // Alternar el estado del usuario (si está activo, lo desactiva y viceversa)
            $user->update(['active' => !$user->active]);
    
            $status = $user->active ? "activado" : "desactivado";
            return $this->response("Usuario {$status} correctamente", 200, false);
        } catch (\Exception $e) {
            return $this->response("Error en el servicio: " . $e->getMessage(), 500, true);
        }
    }

    public function adminExists()
    {
        $exists = User::where('root', true)->exists(); // o el campo que uses para identificar admin
        return $this->response("Respuesta de consulta", 200, false, $exists);
    }

    public function createUserAdmin(CreateAdminUserRequest $request){
        try {
            DB::beginTransaction();
            $data = $request->validated();

            $exists = User::where('root', true)->exists(); 
            if($exists){
                return $this->response("Ya existe un usuario administrador", 400, false);
            }

            $user = User::create([
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'root' => true,
                'active' => true,
            ]);

            $perfil = new Perfil([
                'path_profile' => "",
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
            ]);

            $user->perfil()->save($perfil);

            $user->roles()->attach(1);

            DB::commit();

            $data = $user->load('roles');
            return $this->response("Usuario creado con exito", 200, false, $data);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al crear el usuario administrador: " . $e->getMessage());
            return $this->response("Error en el servicio", 500, false);
        }
    }


}
