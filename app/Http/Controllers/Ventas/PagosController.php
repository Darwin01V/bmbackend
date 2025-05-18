<?php

namespace App\Http\Controllers\Ventas;

use App\Events\PagoRecibido;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ventas\PagoCreateRequest;
use App\Models\Planes;
use App\Models\User;
use App\Models\Ventas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PagosController extends Controller
{
    public function webhook(Request $request){
        try {
            $cantidad = $request->input('amount');
            $cliente = $request->input('customer'); //email - dni
            $suscripcion = $request->input('subscription'); //email - dni
    
            $email = $cliente['email'];
            $dni = $cliente['dni'];
            $nombre_plan = $suscripcion['plan']['name'];
    
            event(new PagoRecibido($email, $dni, $nombre_plan, $cantidad));
    
            return $this->response("Pago procesado", 200, false);
        } catch (\Exception $e) {
            Log::error("Error al procesar la confirmacion del pago". $e->getMessage());
            return $this->response("Error de pago", 200, false);
        }
    }

    public function pagocreated(PagoCreateRequest $request)
    {
        try {
            $user = auth()->user();
            $data = $request->validated();
            $plan = Planes::find($data['plan_id']);
    
            // Buscar si tiene algún pago pendiente
            $ventaPendiente = Ventas::where('user_id', $user->id)
                ->where('estado', 'P')
                ->first();
    
            if ($ventaPendiente) {
                // Si es el mismo plan, solo devuelves "OK"
                if ($ventaPendiente->plan_id === $plan->id) {
                    return $this->response("Ya tienes un pago pendiente para este plan", 200, false);
                }
    
                // Si es otro plan, se cancela la venta anterior
                $ventaPendiente->estado = 'C'; // Cancelado
                $ventaPendiente->save();
            }
    
            // Calcular monto con descuento
            $descuento = $plan->discount_percentage / 100;
            $monto = $plan->price - ($plan->price * $descuento);
    
            // Crear nueva venta
            Ventas::create([
                'date_shop' => Carbon::now(),
                'amount' => $monto,
                'plan_id' => $plan->id,
                'user_id' => $user->id,
                'estado' => 'P',
            ]);
    
            return $this->response("Pago pendiente creado", 200, false);
        } catch (\Exception $e) {
            return $this->response("Error al crear el pago", 200, false);
        }
    }
    
}
