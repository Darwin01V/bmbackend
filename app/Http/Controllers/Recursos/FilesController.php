<?php

namespace App\Http\Controllers\Recursos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Recursos\CreateFileRequest;
use App\Models\Files;
use Illuminate\Http\Request;

class FilesController extends Controller
{
    protected $file;

    public function __construct(Files $files)
    {
        $this->file = $files;
    }

    public function getFiles(){
        try {
            $data = $this->file->with(['artist', 'genre', 'user'])->get();

            return $this->response("Datos obtenidos", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos", 200, true);
        }
    }

    public function getFile($id){
        try {
            $data = $this->file->with(['artist', 'genre', 'user'])->find($id);

            if($data){
                return $this->response("Datos obtenidos", 200, false, $data);
            } else {
                return $this->response("No se encontró el recurso", 200, true);
            }
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos", 200, true);
        }
    }

    public function createFile(CreateFileRequest $request){
        try {
            $data = $request->validated();
            $data['user_id'] = auth()->user()->id;

            $file = $this->file->create($data);

            return $this->response("Recurso creado correctamente", 201, false, $file);
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos", 200, true);
        }
    }

    public function updateFile(CreateFileRequest $request){
        try {
            $data = $request->validated();
            $data['user_id'] = auth()->user()->id;

            $file = $this->file->create($data);

            return $this->response("Recurso creado correctamente", 201, false, $file);
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos", 200, true);
        }
    }

    public function toggleFileStatus($id){
        try {
            $file = $this->file->find($id);

            if($file){

                $file->update(['active' => !$file->active]);
    
                $status = $file->active ? "activado" : "desactivado";
                return $this->response("Artista {$status} correctamente", 200, false);
            } else {
                return $this->response("No se encontró el recurso", 200, true);
            }
        } catch (\Exception $e) {
            return $this->response("Error al desactivar el recurso", 200, true);
        }
    }
}
