<?php

namespace App\Http\Controllers\Recursos;

use App\Events\NuevaDescarga;
use App\Http\Controllers\Controller;
use App\Http\Requests\Recursos\CreateFileRequest;
use App\Http\Requests\Recursos\FileStatusRequest;
use App\Http\Requests\Recursos\UpdateFileRequest;
use App\Models\Files;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FilesController extends Controller
{
    protected $file;

    public function __construct(Files $files){
        $this->file = $files;
    }

    public function getFiles(){
        try {
            $user = auth()->user();
            $rol = $user->roles()->first()->name; // "creador" o "administrador"
    
            // Si es administrador, obtiene todos los archivos; si es creador, solo los suyos
            $query = $this->file->with(['artist', 'genre', 'user', 'user.perfilCreador']);
    
            if ($rol === 'creador') {
                $query->where('user_id', $user->id);
            }
    
            $data = $query->paginate(10);
    
            // Modificar el path para generar la URL correcta
            foreach ($data as $file) {
                $file->path = url('storage/uploads/' . basename($file->path));
            }
    
            return $this->response("Datos obtenidos", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos: " . $e->getMessage(), 500, true);
        }
    }

    public function getFile($id){
        try {
            $data = $this->file->with(['artist', 'genre', 'user'])->find($id);

            if($data){
                return $this->response("Datos obtenidos", 200, false, $data);
            } else {
                return $this->response("No se encontró el recurso", 404, true);
            }
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos", 200, true);
        }
    }

    public function createFiles(CreateFileRequest $request){
        try {
            $user_id = auth()->user()->id;
            $data = $request->validated();
            $uploadedFiles = [];  
            $uploadedFiles_p = [];  
    
            foreach ($data['files'] as $file) {
                if (isset($file['file'])) {

                    $path = $file['file']->store('public/uploads'); 
                    $path_preview = $file['preview']->store('public/uploads'); 
                    $path_image = $file['path_image']->store('public/uploads'); 
                    $uploadedFiles[] = $path; 
                    $uploadedFiles_p[] = $path_preview; 
                    $slider_new = isset($file['slider_new']) ? (int) $file['slider_new'] : 0;

                    Files::create([
                        'path' => $path,
                        'path_preview' => $path_preview,
                        'path_image' => $path_image,
                        'name' => $file['name'] ?? null,
                        'version' => $file['version'] ?? null,
                        'bpm' => $file['bpm'] ?? null,
                        'type' => $file['type'],
                        'slider_new' => $slider_new,
                        'artists_id' => $file['artists_id'] ?? null,
                        'genres_id' => $file['genres_id'] ?? null,
                        'user_id' => $user_id
                    ]);
                }
            }

            return $this->response("Recursos guardados correctamente", 201);
    
        } catch (\Exception $e) {
            foreach ($uploadedFiles as $file) {
                Storage::delete($file);
            }

            foreach ($uploadedFiles_p as $file) {
                Storage::delete($file);
            }
            return $this->response("Error con el servicio de cargar archivos". $e->getMessage(),500);
        }
    }

    public function StatusFile(FileStatusRequest $request){
        try {
            $user_id = auth()->user()->id;
            $data = $request->validated(); //id, status
            $file = $this->file->findOrFail($data['id']);
            
            $file->status = $data['status'];
            
            if ($data['status'] === 'A') {
                $oldPath = storage_path('app/' . $file->path);

    
                if (file_exists($oldPath)) {

                    $newPath = 'uploads/' . basename($file->path);
                    $newStoragePath = storage_path('app/' . $newPath);
    
                    if (rename($oldPath, $newStoragePath)) {
                        $file->path = $newPath;
                    }
                }else{
                    return $this->response("Se actualizo pero no se pudo mover el archivo", 201, false,  $oldPath);
                }
            }

            $file->save();

            return $this->response("Recurso actualizado correctamente", 201);
        } catch (\Exception $e) {
            return $this->response("Error con el servicio de cargar archivos". $e->getMessage(),500);
        }
    }

    public function downlanFile($id){
        try {
            $file = Files::find($id);
            $path = $file->path;
            
            if (Storage::exists($path)) {
                return Storage::download($path, basename($path));
            }
            
        } catch (\Exception $e) {
            Log::error("Error al descargar downlanFile". $e->getMessage());
            return $this->response("Error", 400, true);
        }
    }

    public function verifiquedownload($id){
        try {
            $user = auth()->user();
            $file = Files::find($id);
            $path = $file->path;

            $plan_perfil = $user->perfilplan;
            $plan = $plan_perfil->plan;
            $plan_count = $plan_perfil->plan_count()->first();

            if($file){
                
                if($plan_perfil?->active || empty($plan_perfil)){
                    if(Str::startsWith($file->type, 'audio/')){
                        if($plan->type == 'A'){
                            if(!$plan->unlimited){
                                if($plan_count->n_audios > 0){
                            
                                    if (Storage::exists($path)) {
                                        $plan_count->n_audios = $plan_count->n_audios - 1;
                                        $file->n_downloads = $file->n_downloads + 1;
                                        $file->save();
                                        $plan_count->save();
                                        
                                        $user_c= $file->user;
                                        $rol_user= $user_c->roles;
                                        if($rol_user[0]->name === 'creador'){
                                            $creador = $user_c->perfilCreador;
                                            event(new NuevaDescarga($creador));
                                        }

                                        return $this->response("OK", 200, true);
                                    }
    
                                }else{
                                    return $this->response("No tienes audios disponibles", 404, true);
                                }
                            }else{
                                if (Storage::exists($path)) {
                                    $file->n_downloads = $file->n_downloads + 1;
                                    $file->save();
                                    
                                    $user_c= $file->user;
                                    $rol_user= $user_c->roles;
                                    if($rol_user[0]->name === 'creador'){
                                        $creador = $user_c->perfilCreador;
                                        event(new NuevaDescarga($creador));
                                    }
                                    return $this->response("OK", 200, true);
                                }
                            }
                        }else{
                            return $this->response("No tienes un plan activo para audios", 404, true);
                        }
                    }if (Str::startsWith($file->type, 'video/')) {

                        if($plan->type == 'V'){
                            if(!$plan->unlimited){
                                if($plan_count->n_videos > 0){
                                    if (Storage::exists($path)) {
                                        $plan_count->n_videos = $plan_count->n_videos - 1;
                                        $file->n_downloads = $file->n_downloads + 1;
                                        $file->save();
                                        $plan_count->save();

                                        $user_c= $file->user;
                                        $rol_user= $user_c->roles;
                                        if($rol_user[0]->name === 'creador'){
                                            $creador = $user_c->perfilCreador;
                                            event(new NuevaDescarga($creador));
                                        }
                                        return $this->response("OK", 200, true);
                                    }
    
                                }else{
                                    return $this->response("No tienes videos disponibles", 404, true);
                                }
                            }else{
                                if (Storage::exists($path)) {
                                    $file->n_downloads = $file->n_downloads + 1;
                                    $file->save();
                                    
                                    $user_c= $file->user;
                                    $rol_user= $user_c->roles;
                                    if($rol_user[0]->name === 'creador'){
                                        $creador = $user_c->perfilCreador;
                                        event(new NuevaDescarga($creador));
                                    }
                                    return $this->response("OK", 200, true);
                                }
                            }
                        }else{
                            return $this->response("No tienes un plan activo para videos", 404, true);
                        }
                        
                    } else {
                        return $this->response("Tipo de archivo no soportado", 404, true);
                    }
                    
                }else{
                    return $this->response("No tienes un plan activo", 404, true);
                }
            }else{
                return $this->response("No se encontro el archivo", 200, true);
            }

        } catch (\Exception $e) {
            Log::error("Error al descargar". $e->getMessage());
            return $this->response("Error al descargar ", 505, true);
        }
    }

    public function updateFile(UpdateFileRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $file = $this->file->find($id);
    
            if (!$file) {
                return $this->response("Recurso no encontrado", 404, true);
            }

            if(isset($data['image'])) {
                $path_image = $data['image']->store('public/uploads'); 
                if(isset($file->path_image)) {
                    $oldPath = storage_path('app/' . $file->path_image);
                    if (file_exists($oldPath)) {
                        Storage::delete($oldPath);
                    }
                }
                $data['path_image'] = $path_image;
            }
    
            $file->update($data);
    
            return $this->response("Recurso editado correctamente", 200, false, $file);
        } catch (\Exception $e) {
            // Registra el error para poder rastrearlo
            Log::error('Error al actualizar archivo: '.$e->getMessage());
    
            return $this->response("Error interno al actualizar el recurso", 500, true);
        }
    }

    public function toggleFileStatus($id){
        try {
            $file = $this->file->find($id);

            if($file){

                $file->update(['active' => !$file->active]);
    
                $status = $file->active ? "activado" : "desactivado";
                return $this->response("Recurso {$status} correctamente", 200, false);
            } else {
                return $this->response("No se encontró el recurso", 200, true);
            }
        } catch (\Exception $e) {
            return $this->response("Error al desactivar el recurso", 200, true);
        }
    }

    public function DeleteFile($id){
        try {
            $file = $this->file->find($id);

            if($file){
                
                $path_preview = $file->path_preview;
                $path = $file->path;

                $storagePathPreview = storage_path('app/' . $path_preview);
                $storagePath = storage_path('app/' . $path);

                // Eliminar archivos si existen
                if (File::exists($storagePathPreview)) {
                    File::delete($storagePathPreview);
                }

                if (File::exists($storagePath)) {
                    File::delete($storagePath);
                }
    

                $file->delete(); // Solo esto es necesario para eliminar de la BD

                return $this->response("Recurso eliminado correctamente", 200, false);
            } else {
                return $this->response("No se encontró el recurso", 200, true);
            }
        } catch (\Exception $e) {
            return $this->response("Error al desactivar el recurso", 200, true);
        }
    }

    // Publics
    public function getFilesAudios(){
        try {
            // Capturamos los parámetros de búsqueda de la request
            $search = request()->query('search');
            $artist = request()->query('artist');
            $genre = request()->query('genre');
    
            $data = $this->file
                ->where('type', 'LIKE', 'audio/%')
                ->where('active', 1)
                ->where('status', 'A')
                ->with(['artist', 'genre'])
                ->when($search, function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%");
                })
                ->when($artist, function ($query) use ($artist) {
                    $query->whereHas('artist', function ($q) use ($artist) {
                        $q->where('id', 'LIKE', "%{$artist}%");
                    });
                })
                ->when($genre, function ($query) use ($genre) {
                    $query->whereHas('genre', function ($q) use ($genre) {
                        $q->where('id', 'LIKE', "%{$genre}%");
                    });
                })
                ->paginate(10);
    
            // Transformamos la respuesta
            $data->getCollection()->transform(function ($file) {
                return [
                    'id' => $file->id,
                    'title' => $file->name,
                    'version' => $file->version,
                    'path_preview' => url('storage/uploads/' . basename($file->path_preview)),
                    'path_image' => $file->path_image ? url('storage/uploads/' . basename($file->path_image)) : null,
                    'artist' => $file->artist->name ?? null,
                    'genre' => $file->genre->name ?? null,
                ];
            });
    
            return $this->response("Datos obtenidos", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos: " . $e->getMessage(), 500, true);
        }
    }
       
    public function getFilesVideo(){
        try {
            // Capturamos los parámetros de búsqueda de la request
            $search = request()->query('search');
            $artist = request()->query('artist');
            $genre = request()->query('genre');

            $data = $this->file
                    ->where('type', 'LIKE', 'video/%')
                    ->where('active', 1)
                    ->where('status', 'A')
                    ->with(['artist', 'genre'])
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            })
            ->when($artist, function ($query) use ($artist) {
                $query->whereHas('artist', function ($q) use ($artist) {
                    $q->where('id', 'LIKE', "%{$artist}%");
                });
            })
            ->when($genre, function ($query) use ($genre) {
                $query->whereHas('genre', function ($q) use ($genre) {
                    $q->where('id', 'LIKE', "%{$genre}%");
                });
            })
            ->paginate(10);
    
            $data->getCollection()->transform(function ($file) {
                return [
                    'id' => $file->id,
                    'title' => $file->name,
                    'version' => $file->version,
                    'path_preview' => url('storage/uploads/' . basename($file->path_preview)),
                    'path_image' => $file->path_image ? url('storage/uploads/' . basename($file->path_image)) : null,
                    'artist' => $file->artist->name ?? null,
                    'genre' => $file->genre->name ?? null,
                ];
            });
    
            return $this->response("Datos obtenidos", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos: " . $e->getMessage(), 500, true);
        }
    }

    public function getFilesAudiosDesct(){
        try {
            $data = $this->file
                        ->where('type', 'LIKE', 'audio/%')
                        ->where('active', 1)
                        ->where('status', 'A')
                        ->with(['artist', 'genre'])
                        ->orderBy('n_downloads', 'desc') // Ordenar por número de descargas (descendente)
                        ->limit(5) // Limitar a los primeros 5 resultados
                        ->get();
    
            $data->transform(function ($file) {
                return [
                    'id' => $file->id,
                    'title' => $file->name,
                    'version' => $file->version,
                    'path_preview' => url('storage/uploads/' . basename($file->path_preview)),
                    'path_image' => $file->path_image ? url('storage/uploads/' . basename($file->path_image)) : null,
                    'artist' => $file->artist->name ?? null,
                    'genre' => $file->genre->name ?? null,
                    'downloads' => $file->n_downloads, // Mostrar número de descargas
                ];
            });
    
            return $this->response("Datos obtenidos", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos: " . $e->getMessage(), 500, true);
        }
    }    

    public function getFilesVideoDesct(){
        try {
            $data = $this->file
                    ->where('type', 'LIKE', 'video/%')
                    ->where('active', 1)
                    ->where('status', 'A')
                    ->with(['artist', 'genre'])
                    ->orderBy('n_downloads', 'desc') // Ordenar por número de descargas (descendente)
                    ->limit(5) // Limitar a los primeros 5 resultados
                    ->get();
            
            $data->transform(function ($file) {
                return [
                    'id' => $file->id,
                    'title' => $file->name,
                    'version' => $file->version,
                    'path_preview' => url('storage/uploads/' . basename($file->path_preview)),
                    'path_image' => $file->path_image ? url('storage/uploads/' . basename($file->path_image)) : null,
                    'artist' => $file->artist->name ?? null,
                    'genre' => $file->genre->name ?? null,
                    'downloads' => $file->n_downloads, // Mostrar número de descargas
                ];
            });
    
            return $this->response("Datos obtenidos", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos: " . $e->getMessage(), 500, true);
        }
    }
    
    public function getFilesSliderNuevosLanzamientos(){
        try {
            $data = $this->file
                        ->where('active', 1)
                        ->where('status', 'A')
                        ->where('slider_new', true)
                        ->get();
    
            $data->transform(function ($file) {
                return [
                    'id' => $file->id,
                    'title' => $file->name,
                    'type' => $file->type,
                    'image' => $file->path_image ? url('storage/uploads/' . basename($file->path_image)) : null,
                ];
            });
    
            return $this->response("Datos obtenidos", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos: " . $e->getMessage(), 500, true);
        }
    }  

}
