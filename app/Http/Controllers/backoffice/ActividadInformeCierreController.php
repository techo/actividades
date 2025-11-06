<?php

namespace App\Http\Controllers\backoffice;

use App\Actividad;
use App\ActividadInformeCierre;
use App\Http\Controllers\Controller;

use App\Http\Requests\Actividad\EditarInformeCierre;

class ActividadInformeCierreController extends Controller
{
    // public function show(Actividad $id)
    // {
    //     $actividad = $id;

    //     $edicion = true;

    //     $informes = ActividadInformeCierre::where(['idActividad' => $actividad->idActividad])->get();
    //     return view('backoffice.actividades.informe.show', 
    //         compact(
    //             'actividad',
    //             'informes',
    //             'edicion' 
    //         ) 
    //     );
    // }
    
    public function index(Actividad $id)
    {
        $actividad = $id;

        $datatableInformeConfig = config('datatables.informeCierre');
        $fieldsInforme = json_encode($datatableInformeConfig['fields']);
        $sortOrderInforme = json_encode($datatableInformeConfig['sortOrder']);

        return view('backoffice.actividades.informe.show', 
            compact(
                'actividad',
                'fieldsInforme',
                'sortOrderInforme'
            ) 
        );
    }

    public function getInformes(Actividad $id)
    {
        $informes = ActividadInformeCierre::where('idActividad', $id->idActividad)
            ->get()
            ->map(function ($informe) {
                $informe->total_soluciones = 
                    ($informe->cant_soluciones_voluntariado ?? 0)
                + ($informe->cant_soluciones_corporativos ?? 0)
                + ($informe->cant_soluciones_secundarios ?? 0)
                + ($informe->cant_soluciones_universitarios ?? 0)
                + ($informe->cant_soluciones_familias ?? 0);
            return $informe;
        });; 

        return response()->json($informes);
    }

    public function get(Actividad $id, $idInformeCierre)
    {
        $informe = ActividadInformeCierre::findOrFail($idInformeCierre);

        return response()->json($informe);
    }
    
    public function upsert(EditarInformeCierre $request,Actividad $id)
    {
        if ($request->has('idActividadInformeCierre'))
            $actividadInformeCierre = ActividadInformeCierre::findOrFail($request->idActividadInformeCierre);
        else {
            $actividadInformeCierre = new ActividadInformeCierre();
            $actividadInformeCierre->idActividad = $id->idActividad;
        }
        $validado = $request->validated();
        $actividadInformeCierre->fill($validado);

        $actividadInformeCierre->save();

        return response()->json($actividadInformeCierre->fresh());
    }

    public function delete(Actividad $id, $idInformeCierre)
    {
        $informe = ActividadInformeCierre::findOrFail($idInformeCierre);
        $informe->delete();

        return response()->json(['message' => 'Informe de cierre eliminado correctamente.']);
    }
}
