<?php

namespace App\Listeners;

use App\Events\PagoRecibido;
use App\Models\Planes;
use App\Models\PlanPerfil;
use App\Models\User;
use App\Models\UserPlanCount;
use App\Models\UserPlanHistory;
use App\Models\Ventas;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AsignarPlanAlUsuario
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
    public function handle(PagoRecibido $event): void
    {
        $usuario = User::where('email', $event->email)->first();
        if ($usuario) {
            $sales = Ventas::where('user_id', $usuario->id)->where('estado', 'P')->first();
            $plan = Planes::find($sales->plan_id);

            $plan_perfil = PlanPerfil::create([
                "date_start" => Carbon::now(),
                "date_end" => Carbon::now()->addDays($plan->time),
                "active" => true,
                'user_id' => $usuario->id,
                'plan_id'=> $plan->id,
            ]);

            UserPlanCount::create([
                "user_plan_id" => $plan_perfil->id,
                "n_audios" => $plan->unlimited ? '9999' : $plan->n_audios,
                "n_videos" => $plan->unlimited ? '9999' :$plan->n_videos
            ]);
            
            UserPlanHistory::create([
                "date_shop" => Carbon::now(),
                "amount" => $event->cantidad,
                "plan_id" => $plan->id,
                "user_id" => $usuario->id,
            ]);

            $sales->update(["estado" => 'C']);

        } else {
            Log::error("Usuario no encontrado: {$event->email}");
        }

    }
}
