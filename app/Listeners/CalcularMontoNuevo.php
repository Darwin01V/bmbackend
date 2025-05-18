<?php

namespace App\Listeners;

use App\Events\NuevaDescarga;
use App\Models\MontoCreador;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CalcularMontoNuevo
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NuevaDescarga $event): void
    {
        if($event->creador){
            $creador = $event->creador;
            $monto_creador = new MontoCreador();
            $registro_creador = $monto_creador->where('creador_id', $creador->user_id)->first();

            if($registro_creador){
                $fecha_fin = $registro_creador->fecha_fin;

                if($fecha_fin >= Carbon::now()){
                    // Este registro aún es válido para el mes actual
                    $comision_actual = $creador->comision;
                    $monto_pagar = $registro_creador->monto_mes + $comision_actual * 1;
                    
                    $registro_creador->update([
                        'n_descargas' => $registro_creador->n_descargas + 1,
                        'monto_mes' => $monto_pagar,
                    ]);
                }else{
                    // El registro ha expirado, crear uno nuevo
                    $comision_actual = $creador->comision;
                    $monto_pagar = $comision_actual * 1;
                    $registro_creador->update([
                        'n_descargas' => 1,
                        'monto_mes' => $monto_pagar,
                        'fecha_inicio' => Carbon::now(),
                        'fecha_fin' => Carbon::now()->endOfMonth()->endOfDay(),
                    ]);
                }
            }else{
                $comision_actual = $creador->comision;
                $monto_pagar = $comision_actual * 1;
                $monto_creador->create([
                    "creador_id" => $creador->user_id,
                    'n_descargas' => 1,
                    'monto_mes' => $monto_pagar,
                    'fecha_inicio' => Carbon::now(),
                    'fecha_fin' => Carbon::now()->endOfMonth()->endOfDay(),
                ]);

                Log::info("Se creo el nuevo registro para controlar el pago al creador n: ". $creador->user_id);
            }
        }else{
            Log::info("No hay creador a asignar descarga");
        }
    }
}
