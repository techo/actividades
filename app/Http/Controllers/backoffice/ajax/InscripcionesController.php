<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\Exports\InscripcionesExport;
use App\Inscripcion;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;

class InscripcionesController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id, Request $request)
    {
        $export = new InscripcionesExport($id, $request->filter, $request->sort);
        $collection = $export->collection();
        $result = $this->paginate($collection, 10);
        return $result;

    }

    public function update(Request $request, $id, $inscripcion)
    {
        $inscripcion = Inscripcion::findOrFail($inscripcion);
        $inscripcion->presente = $request->presente;
        $inscripcion->pago = $request->pago;
        if ($request->estado !== null) {
            $inscripcion->estado = $request->estado;
        }

        if ($inscripcion->save()) {
            return response('Ok');
        }

        return response('Ocurrió un error al actualizar el estado', 500);
    }
}
