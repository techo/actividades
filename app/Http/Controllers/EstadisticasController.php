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
        // Canónico: "movilizado" = participaciones con presente=1, ubicadas por la
        // fecha de la actividad (Actividad.fechaInicio), NO por fechaInscripcion.
        $inscriptosPresentes = \App\Inscripcion::join('Actividad', 'Actividad.idActividad', '=', 'Inscripcion.idActividad')
            ->whereYear('Actividad.fechaInicio', now()->year)
            ->where('Inscripcion.presente', 1)
            ->count();

        return $inscriptosPresentes;
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
        // Canónico: personas únicas movilizadas, por fecha de la actividad.
        $personas = \App\Inscripcion::join('Actividad', 'Actividad.idActividad', '=', 'Inscripcion.idActividad')
            ->whereYear('Actividad.fechaInicio', now()->year)
            ->where('Inscripcion.presente', 1)
            ->select(DB::raw('count(distinct Inscripcion.idPersona) as cantidad'))
            ->first();

        return $personas;
    }
}
