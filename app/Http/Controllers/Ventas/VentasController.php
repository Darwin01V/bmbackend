<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Ventas;
use Illuminate\Http\Request;

class VentasController extends Controller
{
    protected $ventas;

    public function __construct(Ventas $ventas){
        $this->ventas = $ventas;
    }
    public function getVentas(){
        try {
            $data = $this->ventas->with(['user', 'plan'])->get();
            return $this->response("Datos obtendidos", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos" . $e->getMessage(), 200, true);
        }  
    }

    public function getVentaByUser($user){
        try {
            $data = $this->ventas->where('user_id', $user)->with(['user', 'plan'])->get();
            return $this->response("Datos obtenidos", 200, false, $data);
        } catch (\Exception $e) {
            return $this->response("Error al obtener los datos" . $e->getMessage(), 200, true);
        }
    }
}
