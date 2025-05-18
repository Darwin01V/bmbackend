<?php

namespace App\Http\Controllers\Administacion;

use App\Http\Controllers\Controller;
use App\Models\Files;
use App\Models\PerfilCreador;
use App\Models\Planes;
use App\Models\PlanPerfil;
use App\Models\Ventas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function getDataAdministrador()
    {
        try {
            // Ventas totales desde sus inicios
            $balance = 0;
            $planes_venta = Ventas::where('estado', 'C')->get();
            foreach ($planes_venta as $plan) {
                $balance += $plan->amount;
            }

            // Ventas totales diarias
            $ventas_diarias = number_format(
                Ventas::where('estado', 'C')
                    ->whereDate('date_shop', now())
                    ->sum('amount'),
                2,
                '.',
                ''
            );

            // Ventas totales pendientes
            $ventas_pendientes = number_format(
                Ventas::where('estado', 'P')
                    ->sum('amount'),
                2,
                '.',
                ''
            );

            // Ventas del mes
            $ventas_mes = number_format(
                Ventas::where('estado', 'C')
                    ->whereMonth('date_shop', now()->month)
                    ->sum('amount'),
                2,
                '.',
                ''
            );

            $clientes_con_plan = PlanPerfil::where('active', true)->get()->count();
            $planes = Planes::where('active', true)->count();
            $descargas_totales = 0;
            $files= Files::all();

            foreach ($files as $file) {
                $descargas_totales += $file->downloads;
            }
            
            $creadores = PerfilCreador::all()->count();

            $data = [
                'cards' => [
                    [
                        'title' => 'Balance total',
                        'value' => $balance,
                        'icon' => 'pi pi-wallet'
                    ],
                    [
                        'title' => 'Ventas diarias',
                        'value' => $ventas_diarias,
                        'icon' => 'pi pi-shopping-bag'
                    ],
                    [
                        'title' => 'Ventas pendientes',
                        'value' => $ventas_pendientes,
                        'icon' => 'pi pi-clock'
                    ],
                    [
                        'title' => 'Ventas del mes',
                        'value' => $ventas_mes,
                        'icon' => 'pi pi-calendar-plus'
                    ]
                ],
                'general' => [
                    [
                        'title' => 'Clientes activos',
                        'value' => $clientes_con_plan,
                        'icon' => 'pi pi-users'
                    ],
                    [
                        'title' => 'Planes Activos',
                        'value' => $planes,
                        'icon' => 'pi pi-file-plus'
                    ],
                    [
                        'title' => 'Descargas totales',
                        'value' => $descargas_totales,
                        'icon' => 'pi pi-cloud-download'
                    ],
                    [
                        'title' => 'Creadores de contenido',
                        'value' => $creadores,
                        'icon' => 'pi pi-user-plus'
                    ]
                ]
            ];

            return $this->response("Datos obtendidos con exito", 200,false , $data);
        } catch (\Exception $e) {
            Log::error('Error al obtener los datos del dashboard: ' . $e->getMessage());
            return $this->response("Error al obtener los datos", 500, null);
        }
    }

    public function getDataCreador()
    {
        try {
            $user = auth()->user();
            // Ventas totales desde sus inicios
            $balance = 0;
            $files= Files::where('user_id', $user->id)->get();
            $perfil_creador = PerfilCreador::where('user_id', $user->id)->first();
            
            foreach ($files as $file) {
                $balance += $file->downloads * $perfil_creador->comision;
            }

            // Recursos totales por user
            $recursos_totales = 0;
            $recursos_totales= Files::where('user_id', $user->id)->get()->count();
            
            // Descargas totales
            $descargas_totales= 0;
            foreach($files as $file){
                $descargas_totales += $file->downloads;
            }

            $data = [
                'cards' => [
                    [
                        'title' => 'Balance total',
                        'value' => $balance,
                        'icon' => 'pi pi-wallet',
                        'type' => 'F'
                    ],
                    [
                        'title' => 'Recursos Totales',
                        'value' => $recursos_totales,
                        'icon' => 'pi pi-file',
                        'type' => ''
                        
                    ],
                    [
                        'title' => 'Comision Actual',
                        'value' => $perfil_creador->comision,
                        'icon' => 'pi pi-dollar',
                        'type' => 'F'
                    ],
                    [
                        'title' => 'Descargas Totales',
                        'value' => $descargas_totales,
                        'icon' => 'pi pi-cloud-download',
                        'type' => ''
                    ]
                ],

            ];

            return $this->response("Datos obtendidos con exito", 200,false , $data);
        } catch (\Exception $e) {
            Log::error('Error al obtener los datos del dashboard: ' . $e->getMessage());
            return $this->response("Error al obtener los datos", 500, null);
        }
    }
}
