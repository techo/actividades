<?php

namespace App\Http\Controllers\backoffice;

use App\Actividad;
use App\Pais;
use App\PuntoEncuentro;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ActividadesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $datatableConfig = config('datatables.actividades');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        isset($request->msg) ? $mensaje = 'La actividad se eliminó correctamente' : $mensaje = '';
        return view('backoffice.actividades.index', compact('fields', 'sortOrder', 'mensaje'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $edicion = false;
        $paises = Pais::all();
        $actividad = Actividad::findOrFail($id);
        $actividad->load(
            'tipo.categoria',
            'unidadOrganizacional',
            'modificadoPor',
            'puntosEncuentro',
            'pais',
            'provincia',
            'localidad'
        );

        try {
            $provincias = $actividad->pais->provincias;
            $localidades = $actividad->provincia->localidades;

        } catch (\Exception $e) {
            $provincias = null;
            $localidades = null;
        }
        return view(
            'backoffice.actividades.show',
            compact(
                'actividad',
                'paises',
                'coordinadores',
                'provincias',
                'localidades',
                'edicion'
            )
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $res = strtotime($request['fechaInicio']);
        // Hacer validación de datos

        $v = Validator::make(
            $request->all(),
            [
            'costo' => 'numeric | min:0',
            'descripcion' => 'required',
            'fechaInicio' => 'required | date',
//            'fechaInicioEvaluaciones' => 'required_if:tipo.flujo, CONSTRUCCION | date',
            'fechaInicioInscripciones' => 'required | date',
            'fechaFin' => 'required | date',
//            'fechaFinEvaluaciones' => 'required_if:tipo.flujo, CONSTRUCCION | date',
            'fechaFinInscripciones' => 'required | date',
//            'idFormulario' => 'required_if:tipo.flujo, CONSTRUCCION | numeric',
            'idTipo' => 'required',
            'inscripcionInterna' => 'required',
            'localidad.id' => 'required',
//            'LinkPago' => 'url',
            'limiteInscripciones' => 'numeric',
            'mensajeInscripcion' => 'required',
//            'modificado_por.idPersona' => 'required',
            'nombreActividad' => 'required',
            'pais.id' => 'required',
            'provincia.id' => 'required',
            'tipo.categoria.id' => 'required',
            'unidad_organizacional.idUnidadOrganizacional' => 'required',
            ]
        );

        $v->sometimes('idFormulario', 'required|numeric', function ($request) {

            return $request['tipo']['flujo'] == 'CONSTRUCCION';
        });
        $v->sometimes('fechaInicioEvaluaciones', 'required|date', function ($request) {

            return $request['tipo']['flujo'] == 'CONSTRUCCION';
        });
        $v->sometimes('fechaFinEvaluaciones', 'required|date', function ($request) {

            return $request['tipo']['flujo'] == 'CONSTRUCCION';
        });
        $v->sometimes('LinkPago', 'url', function ($request) {

            return $request['tipo']['flujo'] == 'CONSTRUCCION';
        });

//        $request->validate([
//            'costo' => 'numeric | min:0',
//            'descripcion' => 'required',
//            'fechaInicio' => 'required | date',
//            'fechaInicioEvaluaciones' => 'required_if:tipo.flujo, CONSTRUCCION | date',
//            'fechaInicioInscripciones' => 'required | date',
//            'fechaFin' => 'required | date',
//            'fechaFinEvaluaciones' => 'required_if:tipo.flujo, CONSTRUCCION | date',
//            'fechaFinInscripciones' => 'required | date',
//            'idFormulario' => 'required_if:tipo.flujo, CONSTRUCCION | numeric',
//            'idTipo' => 'required',
//            'inscripcionInterna' => 'required',
//            'localidad.id' => 'required',
//            'LinkPago' => 'url',
//            'limiteInscripciones' => 'numeric',
//            'mensajeInscripcion' => 'required',
//            'modificado_por.idPersona' => 'required',
//            'nombreActividad' => 'required',
//            'pais.id' => 'required',
//            'provincia.id' => 'required',
//            'tipo.categoria.id' => 'required',
//            'unidad_organizacional.idUnidadOrganizacional' => 'required',
//        ]);

        if ($v->passes()) {
            $actividad = Actividad::find($id);

            foreach ($request->except('idActividad',
                'casasPlanificadas',
                'casasConstruidas',
                'comentarios',
                'tipoConstruccion',
                'idListaCTCT',
                'modificado_por.idPersona'
            )
                     as $field => $value) {

                $esFecha = in_array($field, $actividad->getDates());

                if (!is_array($value) && isset($actividad->{$field}) && $esFecha) {
                    $value = Carbon::parse($value)->format('Y-m-d');
                    $actividad->{$field} = $value;
                }

                if (!is_array($value) && isset($actividad->{$field}) && !$esFecha) {
                    $actividad->{$field} = $value;
                }

            }

            $actividad->estadoConstruccion = ($request->estadoConstruccion) ? "Abierta" : "Cerrada";
            $actividad->idPais = $request['pais']['id'];
            $actividad->idProvincia = $request['provincia']['id'];
            $actividad->idLocalidad = $request['localidad']['id'];
//        $actividad->idTipo = $request['tipo']['idTipo'];
            $actividad->idUnidadOrganizacional = $request['unidad_organizacional']['idUnidadOrganizacional'];
            $actividad->idPersonaModificacion = $request['modificado_por']['idPersona'];

            if ($actividad->save()) {
                foreach ($request->puntos_encuentro as $punto) {
                    if (!isset($punto['idPuntoEncuentro'])) {
                        $p = new PuntoEncuentro();
                        $p->punto = $punto['punto'];
                        $p->horario = $punto['horario'];
                        $p->idActividad = $actividad->idActividad;
                        $p->idLocalidad = $punto['idLocalidad'];
                        $p->idPais = $punto['idPais'];
                        $p->idProvincia = $punto['idProvincia'];
                        $p->idPersona = $punto['responsable']['id'];
                        $p->save();
                    }
                }

                foreach ($request->puntosEncuentroBorrados as $borrado) {
                    $punto = PuntoEncuentro::find($borrado['idPuntoEncuentro']);
                    $punto->delete();
                }
            }

            //Grabar/Sincronizar puntos de encuentro

            return response('Actividad guardada correctamente.', 200);

        }

        return response($v->errors()->all(), 422);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $actividad = Actividad::find($id);
            $actividad->delete();
        } catch (\Exception $exception) {
            return response($exception->getMessage(), 500);
        }
        return redirect()->action('backoffice\ActividadesController@index')
            ->with('status', 'La actividad se eliminó correctamente');
    }
}
