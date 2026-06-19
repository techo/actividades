<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstadisticasController extends Controller
{

    public function voluntades_movilizadas()
    {
        return \App\Reporting\MovilizacionMetrics::movilizadosTotal(now()->year);
    }

    public function actividades()
    {
        // Conteo de actividades realizadas en el año = por fecha de la actividad.
        $actividades = \App\Actividad::join('Tipo', 'Tipo.idTipo', '=', 'Actividad.idTipo')
            ->join('atl_CategoriaActividad', 'atl_CategoriaActividad.id', '=', 'Tipo.idCategoria')
            ->select(DB::raw('atl_CategoriaActividad.nombre, count(*) cantidad'))
            ->whereYear('Actividad.fechaInicio', now()->year)
            ->whereIn('atl_CategoriaActividad.id', ['2', '1'])
            ->groupBy('atl_CategoriaActividad.nombre')
            ->orderBy('cantidad', 'DESC');


        return $actividades->get();
    }

    public function personas_movilizadas()
    {
        // Se preserva la forma de respuesta {cantidad} que consume estadisticas-publicas.vue.
        return ['cantidad' => \App\Reporting\MovilizacionMetrics::personasUnicas(now()->year)];
    }
}
