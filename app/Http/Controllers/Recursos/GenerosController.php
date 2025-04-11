<?php

namespace App\Http\Controllers\Recursos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Recursos\CreateGenerosRequest;
use App\Models\Generos;
use Illuminate\Http\Request;

class GenerosController extends Controller
{
    protected $generos;

    public function __construct(Generos $generos){
        $this->generos = $generos;
    }
    public function getGeneros(){
        try {
            $data = $this->generos->paginate(15);
            
            return $this->response("Datos obtenidos", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos ". $e->getMessage(), 500, true);
        }
    }

    public function getGenero($id){
        try {
            $data = $this->generos->find($id);
            
            if($data){
                return $this->response("Datos obtenidos", 200, false, $data);
            } else {
                return $this->response("No se encontró el generos", 404, true);
            }
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos ". $e->getMessage(), 500, true);
        }
    }

    public function createGenero(CreateGenerosRequest $request){
        try {
            $data_validate = $request->validated();
            $data = $this->generos->create($data_validate);
            
            return $this->response("Genero creado", 201, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al crear el generos ". $e->getMessage(), 500, true);
        }
    }

    public function updateGenero(CreateGenerosRequest $request, $id){
        try {
            $data_validate = $request->validated();
            $data = $this->generos->find($id);
            
            if($data){
                $data->update($data_validate);
                return $this->response("Genero actualizado", 200, false, $data);
            } else {
                return $this->response("No se encontró el generos", 404, true);
            }
        } catch (\Exception $e) {
            return $this->response("Error al actualizar el generos ". $e->getMessage(), 500, true);
        }
    }

    public function toggleGeneroStatus($id){
        try {
            $registro =  $this->generos->find($id);
            if (!$registro) {
                return $this->response("Genero no encontrado", 404, true);
            }
    
            // Alternar el estado del usuario (si está activo, lo desactiva y viceversa)
            $registro->update(['active' => !$registro->active]);
    
            $status = $registro->active ? "activado" : "desactivado";
            return $this->response("Genero {$status} correctamente", 200, false);
        } catch (\Exception $e) {
            return $this->response("Error en el servicio: " . $e->getMessage(), 500, true);
        }
    }

    // Public
    public function getGenerosActive(){
        try {
            $data = $this->generos->where('active', 1)->get();
            
            return $this->response("Datos obtenidos", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos ". $e->getMessage(), 500, true);
        }
    }
}
