<?php

namespace App\Http\Controllers\Recursos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Recursos\CreateArtistaRequest;
use App\Models\Artistas;
use Illuminate\Http\Request;

class ArtistasController extends Controller
{
    protected $artista;

    public function __construct(Artistas $artista){
        $this->artista = $artista;
    }
    public function getArtistas(){
        try {
            $data = $this->artista->paginate(5);
            
            return $this->response("Datos obtenidos", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos ". $e->getMessage(), 500, true);
        }
    }

    public function getArtista($id){
        try {
            $data = $this->artista->find($id);
            
            if($data){
                return $this->response("Datos obtenidos", 200, false, $data);
            } else {
                return $this->response("No se encontró el artista", 404, true);
            }
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos ". $e->getMessage(), 500, true);
        }
    }

    public function createArtista(CreateArtistaRequest $request){
        try {
            $data_validate = $request->validated();
            $data = $this->artista->create($data_validate);
            
            return $this->response("Artista creado", 201, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al crear el artista ". $e->getMessage(), 500, true);
        }
    }

    public function updateArtista(CreateArtistaRequest $request, $id){
        try {
            $data_validate = $request->validated();
            $data = $this->artista->find($id);
            
            if($data){
                $data->update($data_validate);
                return $this->response("Artista actualizado", 200, false, $data);
            } else {
                return $this->response("No se encontró el artista", 404, true);
            }
        } catch (\Exception $e) {
            return $this->response("Error al actualizar el artista ". $e->getMessage(), 500, true);
        }
    }

    public function toggleArtistaStatus($id){
        try {
            $registro =  $this->artista->find($id);
            if (!$registro) {
                return $this->response("Artista no encontrado", 404, true);
            }
    
            // Alternar el estado del usuario (si está activo, lo desactiva y viceversa)
            $registro->update(['active' => !$registro->active]);
    
            $status = $registro->active ? "activado" : "desactivado";
            return $this->response("Artista {$status} correctamente", 200, false);
        } catch (\Exception $e) {
            return $this->response("Error en el servicio: " . $e->getMessage(), 500, true);
        }
    }

    // Rutas publics
    public function getArtistasActive(){
        try {
            $data = $this->artista->where('active', 1)->get();
            
            return $this->response("Datos obtenidos", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos ". $e->getMessage(), 500, true);
        }
    }
}
