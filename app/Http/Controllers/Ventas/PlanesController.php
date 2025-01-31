<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ventas\CreatePlanRequest;
use App\Models\Planes;
use Illuminate\Http\Request;

class PlanesController extends Controller
{
    protected $planes;

    public function __construct(Planes $planes){
        $this->planes = $planes;
    }
    public function getPlanes(){
        try {
            $data = $this->planes->all();

            return $this->response("Datos obtenidos", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al obtener los planes" . $e->getMessage(), 500, true, null, );
        }
    }

    public function getPlanesByActive(){
        try {
            $data = $this->planes->where('active', true)->get();

            return $this->response("Datos obtenidos", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al obtener los planes activos" . $e->getMessage(), 500, true, null, );
        }
    }

    public function createPlan(CreatePlanRequest $request){
        try {
            $data_request = $request->validated();

            $data = $this->planes->create($data_request);

            return $this->response("Plan creado con exito", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al crear los planes" . $e->getMessage(), 500, true, null, );
        }
    }

    public function updatePlan(CreatePlanRequest $request, $id){
        try {
            $plan = $this->planes->find($id);

            if (!$plan) {
                return $this->response("Plan no encontrado", 404, true);
            }
            $data_request = $request->validated();    
            $plan->update($data_request);
            return $this->response("Plan actualizado con éxito", 200, false, $plan);
        } catch (\Exception $e) {
            return $this->response("Error al crear los planes" . $e->getMessage(), 500, true, null, );
        }
    }

    public function togglePlanStatus($id){
        try {
            $plan = $this->planes->find($id);

            if($plan){
                $plan->update(['active' => !$plan->active]);
                $status = $plan->active ? "activado" : "desactivado";
                return $this->response("Plan {$status} correctamente", 200, false);
            }

            return $this->response("Plan no encontrado", 404, true, null, );
        } catch (\Exception $e) {
            return $this->response("Error al modificar el estado del plan" . $e->getMessage(), 500, true, null, );
        }
    }
}
