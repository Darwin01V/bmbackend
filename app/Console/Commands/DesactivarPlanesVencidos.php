<?php

namespace App\Console\Commands;

use App\Models\PlanPerfil;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DesactivarPlanesVencidos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // php artisan app:desactivar-planes-vencidos
    protected $signature = 'app:desactivar-planes-vencidos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $vencidos = PlanPerfil::where('active', true)
            ->where('date_end', '<', now())
            ->get();
    
        foreach ($vencidos as $plan) {
            $plan->active = false;
            $plan->save();
            Log::info("Plan desactivado automáticamente para user_id: {$plan->user_id}");
        }

        $enCincoDias = PlanPerfil::where('active', true)
        ->whereDate('date_end', now()->addDays(5)->toDateString())
        ->get();

        foreach ($enCincoDias as $plan) {
            $user = $plan->user; // Asumiendo que tienes una relación user()
    
            if ($user) {
                $user->notify(new \App\Notifications\PlanCercaDeVencimientoNotification($plan));
                Log::info("Notificación enviada a user_id: {$user->id} - Plan vence el: {$plan->date_end}");
            }
        }
    }
}
