<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfigUpdateRequest;
use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ConfigurationController extends Controller
{
    public function getConfiguration(){
        $data = Configuration::all();
        return $this->response("Datos de configuracion", 200, false, $data);
    }

    public function updateConfig($id, ConfigUpdateRequest $request){
        try {
            $data = $request->validated();

            $config = Configuration::find($id);
            Storage::disk('public')->delete($config->value);

            $path_preview = $data['value']->store('uploads', 'public');
            $data['value'] = 'storage/' . $path_preview;
    
            $config->update($data);

            return $this->response("Configuracion editada", 200, false);
        } catch (\Exception $e) {
            Log::error('Error updating configuration: ' . $e->getMessage());
            return $this->response("Configuracion error", 500, true);
        }
    }
}
