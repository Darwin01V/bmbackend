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
            
            if($sales){
                $plan = Planes::find($sales->plan_id);
                $plan_old_current = PlanPerfil::where('user_id', $usuario->id)->first();

                UserPlanHistory::create([
                    "date_shop" => Carbon::now(),
                    "amount" => $event->cantidad,
                    "plan_id" => $plan->id,
                    "user_id" => $usuario->id,
                ]);

                if($plan_old_current){
                    $user_plan_count = UserPlanCount::where("user_plan_id", $plan_old_current->id)->first();

                    if($sales->date_shop <= $plan_old_current->date_end){
                        if ($user_plan_count) {
                            $user_plan_count->update([
                                "n_audios" => $plan->unlimited ? '9999' : $user_plan_count->n_audios + $plan->n_audios,
                                "n_videos" => $plan->unlimited ? '9999' : $user_plan_count->n_videos + $plan->n_videos
                            ]);
                        } else {
                            Log::warning("No se encontró UserPlanCount para el plan: {$plan_old_current->id}");
                        }


                    }else{
                        if ($user_plan_count) {
                            $user_plan_count->update([
                                "n_audios" => $plan->unlimited ? '9999' : $plan->n_audios,
                                "n_videos" => $plan->unlimited ? '9999' : $plan->n_videos
                            ]);
                        } else {
                            Log::warning("No se encontró UserPlanCount para el plan: {$plan_old_current->id}");
                        }

                        $plan_old_current->update([
                            "date_start" => Carbon::now(),
                            "date_end" => Carbon::now()->addDays($plan->time),
                            "active" => true,
                            'plan_id' => $plan->id,
                        ]);
                    }

                }else{
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
                }
                
                $sales->update(["estado" => 'C']);
            }else{ //Renovacion automatico
                $plan_old_current = PlanPerfil::where('user_id', $usuario->id)->where('active', 1)->first();

                if ($plan_old_current) {
                    $plan = Planes::find($plan_old_current->plan_id);
        
                    $plan_old_current->update([
                        "date_start" => Carbon::now(),
                        "date_end" => Carbon::now()->addDays($plan->time),
                        "active" => true,
                        // 'plan_id' => $plan->id,
                    ]);

                    $user_plan_count = UserPlanCount::where("user_plan_id", $plan_old_current->id)->first();
        
                    if ($user_plan_count) {
                        $user_plan_count->update([
                            "n_audios" => $plan->unlimited ? '9999' : $plan->n_audios,
                            "n_videos" => $plan->unlimited ? '9999' : $plan->n_videos
                        ]);
                    } else {
                        Log::warning("No se encontró UserPlanCount para el plan: {$plan_old_current->id}");
                    }
            
                    UserPlanHistory::create([
                        "date_shop" => Carbon::now(),
                        "amount" => $event->cantidad,
                        "plan_id" => $plan->id,
                        "user_id" => $usuario->id,
                    ]);

                    Log::info("Plan renovador para => {$usuario->email}");
                } else {
                    Log::warning("No se encontró plan activo para renovación del usuario: {$usuario->email}");
                }
            }

        } else {
            Log::error("Usuario no encontrado: {$event->email}");
        }

    }
}
