<?php

namespace App\Http\Controllers\backoffice;

use App\Actividad;
use App\ActividadInformeCierre;
use App\Http\Controllers\Controller;

use App\Http\Requests\Actividad\EditarInformeCierre;

class ActividadInformeCierreController extends Controller
{
    public function show(Actividad $id)
    {
        $actividad = $id;

        $edicion = true;

        $informe = ActividadInformeCierre::firstOrNew(['idActividad' => $actividad->idActividad]);
        return view('backoffice.actividades.informe.show', 
            compact(
                'actividad',
                'informe',
                'edicion' 
            ) 
        );
    }

    public function upsert(EditarInformeCierre $request,Actividad $id)
    {
        $actividadInformeCierre = ActividadInformeCierre::firstOrNew([
            'idActividad' => $id->idActividad,]);
        $validado = $request->validated();
        $actividadInformeCierre->fill($validado);

        $actividadInformeCierre->save();

        return response()->json($actividadInformeCierre->fresh());

    }
}
