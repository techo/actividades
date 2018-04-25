<?php

namespace App\Http\Controllers\backoffice\ajax;

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
    public function index(Request $request)
    {
        $sortDefault = 'nombres|asc';
        $request->sort = $request->sort ?? $sortDefault;
        $sort = explode('|', $request->sort);
        list($sortField, $sortOrder) = $sort;
        DB::enableQueryLog();
        $query = DB::table('Persona')
            ->join('Inscripcion', 'Persona.idPersona', '=', 'Inscripcion.idPersona')
            ->join('Actividad', 'Inscripcion.idActividad', '=', 'Actividad.idActividad')
            ->select(
                [
                    'Inscripcion.idPersona',
                    'Persona.dni',
                    'Persona.nombres',
                    'Persona.apellidoPaterno',
                    'Persona.telefonoMovil',
                    'Persona.mail',
                    'Inscripcion.fechaInscripcion',
                    'Inscripcion.idInscripcion AS id',
                    'Inscripcion.pago',
                    'Inscripcion.presente',
                    'Inscripcion.estado',
                    'Actividad.costo',
                    'Actividad.idActividad'
                ]
            )
            ->where('Inscripcion.idActividad', $request->id)
            ->where('Inscripcion.estado', '<>', 'Desinscripto')
            ->orderBy($sortField, $sortOrder);

        if ($request->has('filter')) {
            $query->where(function ($query) use ($request) {
                $query->orWhere('Persona.nombres', 'like', '%' . $request->filter . '%');
                $query->orWhere('Persona.apellidoPaterno', 'like', '%' . $request->filter . '%');
                $query->orWhere('Persona.dni', 'like', '%' . $request->filter . '%');
                $query->orWhere('Persona.mail', 'like', '%' . $request->filter . '%');
                if (strtolower($request->filter) === 'pagado') {
                    $query->orWhere('Inscripcion.pago', 1);
                }
                if (strtolower($request->filter) === 'pendiente') {
                    $query->orWhere('Inscripcion.pago', 0);
                    $query->orWhereNull('Inscripcion.pago');
                }
                if (strtolower($request->filter) === 'presente') {
                    $query->orWhere('Inscripcion.presente', 1);
                }
                if (strtolower($request->filter) === 'ausente') {
                    $query->orWhere('Inscripcion.presente', 0);
                    $query->orWhereNull('Inscripcion.presente');
                }
            });
        }

        $query = $query->get();
        $query = $this->paginate($query, 10);
        \Illuminate\Support\Facades\Log::info(DB::getQueryLog());
        return $query;

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
