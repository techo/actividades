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
        $inscriptosPresentes = \App\Inscripcion::whereYear('fechaInscripcion', now()->year)
        ->where('presente', 1)
        ->count();



        return $inscriptosPresentes;
    }

    public function actividades()
    {
        $actividades = \App\Actividad::join('Tipo', 'Tipo.idTipo', '=', 'Actividad.idTipo')
            ->join('atl_CategoriaActividad', 'atl_CategoriaActividad.id', '=', 'Tipo.idCategoria')
            ->select(DB::raw('atl_CategoriaActividad.nombre, count(*) cantidad'))
            ->whereYear('fechaCreacion', now()->year) 
            ->whereIn('atl_CategoriaActividad.id', ['1', '2'])
            ->groupBy('atl_CategoriaActividad.nombre');

        
        return $actividades->get();
    }

    public function personas_movilizadas()
    {
        $personas = \App\Persona::join('Inscripcion', 'Persona.idPersona', '=', 'Inscripcion.IdPersona')
            ->whereYear('Inscripcion.fechaInscripcion', now()->year)
            ->where('Inscripcion.presente', 1)
            ->groupBy('Persona.idPersona')
            ->select(DB::raw('Persona.idPersona, count(*)'))
            ->get();

        return $personas; 
    }
}
