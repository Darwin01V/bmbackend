<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Perfil;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{   
    protected $user;

    public function __construct(User $user){
        $this->user = $user;
    }

    public function getUsers(){
        try {
            $data = $this->user->with(['perfil','roles'])->get();
            return $this->response("Datos obtendios", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error en el servicio" . $e->getMessage(), 200, false);
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

            $role = Roles::find($data['roles']['id']);
            if (!$role) {
                return $this->response("El rol no existe", 400, true);
            }

            $user = User::create([
                'email' => $data['user']['email'],
                'password' => bcrypt($data['user']['password']),
                'root' => $data['user']['root'] ?? false,
                'active' => $data['user']['active'] ?? true,
            ]);

            $perfil = new Perfil([
                'path_profile' => $data['perfil']['path_profile'] ?? null,
                'first_name' => $data['perfil']['first_name'],
                'last_name' => $data['perfil']['last_name'],
            ]);

            $user->perfil()->save($perfil);

            $user->roles()->attach($data['roles']['id']);

            DB::commit();

            $data = $user->load('perfil', 'roles');
            return $this->response("Usuario creado con exito", 200, false, $data);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response("Error en el servicio" . $e->getMessage(), 200, false);
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

            // Verificar que el rol exista
            $role = Roles::find($data['roles']['id']);
            if (!$role) {
                return $this->response("El rol no existe", 400, true);
            }

            // Actualizar usuario
            $user->update([
                'email' => $data['user']['email'],
                'root' => $data['user']['root'] ?? false,
                'active' => $data['user']['active'] ?? true,
            ]);

            // Si se envía una nueva contraseña, actualizarla
            if (!empty($data['user']['password'])) {
                $user->update(['password' => bcrypt($data['user']['password'])]);
            }

            // Actualizar perfil
            $user->perfil()->update([
                'path_profile' => $data['perfil']['path_profile'] ?? null,
                'first_name' => $data['perfil']['first_name'],
                'last_name' => $data['perfil']['last_name'],
            ]);

            // Actualizar rol (asumimos que es una relación muchos a muchos)
            $user->roles()->sync([$data['roles']['id']]);

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
}
