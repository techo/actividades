<?php

namespace App\Http\Controllers\backoffice;

use App\Actividad;
use App\CategoriaActividad;
use App\Http\Resources\CoordinadorResource;
use App\Persona;
use Carbon\Carbon;
use App\Pais;
use App\PuntoEncuentro;
use App\UnidadOrganizacional;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

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
        isset($request->msg) ? Session::flash('mensaje', 'La actividad se eliminó correctamente') : false;
//        isset($request->ok) ? Session::flash('mensaje', 'La actividad se creó correctamente') : false;
        return view('backoffice.actividades.index', compact('fields', 'sortOrder', 'mensaje'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $edicion = true;
        $paises = Pais::all();
        $columns = Schema::getColumnListing('Actividad');
        $excluidas =
            [
                'pApMat',
                'pDNI',
                'pFonoMovil',
                'pUniversidad',
                'pCarrera',
                'pAnoEstudio',
                'pAcompanante',
                'tPortugues',
                'enviarMail',
                'compromiso'
            ];
        $columns = array_diff($columns, $excluidas);
        $arrayColumnas = array_fill_keys($columns, null);


        $actividad = json_encode($arrayColumnas);
        $categorias = CategoriaActividad::all();
        $tipos = $categorias->first()->tipos; //dd($actividad);
        return view(
            'backoffice.actividades.create',
            compact(
                'actividad',
                'paises',
                'edicion',
                'tipos',
                'categorias'
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $actividad = new Actividad();
        $validator = $this->createValidator($request);
        if ($validator->passes()) {
            if ($this->guardarActividad($request, $actividad)) {
                $request->session()->put('mensaje', 'La actividad se creó correctamente');
                return response('Actividad guardada correctamente.', 200);
            } else {
                return response('No se pudo guardar la actividad', 500);
            }
        }

        return response($validator->errors()->all(), 422);

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
        $actividad = Actividad::with(
            [
                'tipo.categoria',
                'oficina',
                'modificadoPor',
                'puntosEncuentro',
                'pais',
                'provincia',
                'localidad',
                'coordinador:idPersona,nombres,apellidoPaterno,dni'
            ]
        )->where('idActividad', $id)->first();

        if ($actividad) {
            if ($actividad->coordinador) {
                $actividad->coordinador->nombre = $actividad->coordinador->nombreCompleto;
            }

            $categorias = CategoriaActividad::all();
            $tipos = $actividad->tipo->categoria->tipos;
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
                    'provincias',
                    'localidades',
                    'edicion',
                    'tipos',
                    'categorias'
                )
            );
        }

        abort(404);
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
        $actividad = Actividad::findOrFail($id);
        $validator = $this->createValidator($request);
        if ($validator->passes()) {
            if ($this->guardarActividad($request, $actividad)) {
                $request->session()->put('mensaje', 'La actividad se creó correctamente');
                return response('Actividad guardada correctamente.', 200);
            } else {
                return response('No se pudo guardar la actividad', 500);
            }
        }
        return response($validator->errors()->all(), 422);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $actividad = Actividad::findOrFail($id);

        if ($actividad->fechaInicio < Carbon::today()) {
            Session::flash('error', 'No se puede eliminar una actividad que ya ha concluido.');
            return redirect()->back();
        }


        try {
            $actividad->delete();

        } catch (\Exception $exception) {
            return response($exception->getMessage(), 500);
        }
        Session::flash('mensaje', 'La actividad se eliminó correctamente');
        return redirect()->to('/admin/actividades');
    }

    /**
     * @param Request $request
     * @return mixed
     */
    private function createValidator(Request $request)
    {
        $messages = [
            'coordinador.*' => 'El campo coordinador de la actividad es requerido',
            'fechaInicioInscripciones.before' => 'La fecha de inicio de las inscripciones debe ser anterior al inicio de la actividad',
            'fechaFin.after' => 'La fecha fin debe ser posterior a la fecha de inicio de la actividad',
            'fechaFinInscripciones.after' => 'La fecha de fin de las inscripciones debe ser posterior a la fecha de inicio de las inscripciones',
            'fechaFinInscripciones.before' => 'Las inscripciones deben finalizar antes del inicio de la actividad',
            'inscripcionesInternas' => 'visibilidad de las inscripciones',
            'idTipo.*' => 'Debe seleccionar una categoría y un tipo de actividad',
            'localidad.*' => 'El campo localidad debe tener un valor válido',
            'oficina.*' => 'El campo Oficina es requerido',
            'provincia.*' => 'Debe seleccionar la provincia de la actividad',
            'limiteInscripciones.*' => 'El límite máximo de voluntarios debe tener un valor numérico válido',
            'nombreActividad.*' => 'La actividad debe tener un nombre',
            'pais.*' => 'Debe seleccionar el país de la actividad',
            'costo.*' => 'Debe especificar el costo de participar en la construcción',
        ];
        $v = Validator::make(
            $request->all(),
            [
                'coordinador.idPersona' => 'required | numeric',
                'descripcion' => 'required',
                'fechaInicio' => 'required | date',
                'fechaInicioInscripciones' => 'required | date | before_or_equal:fechaInicio',
                'fechaFin' => 'required | date | after_or_equal:fechaInicio',
                'fechaFinInscripciones' => 'required | date | after_or_equal:fechaInicioInscripciones | before_or_equal:fechaInicio',
                'idTipo' => 'required',
                'inscripcionInterna' => 'required',
                'localidad.id' => 'required',
                'limiteInscripciones' => 'numeric',
                'mensajeInscripcion' => 'required',
                'nombreActividad' => 'required',
                'pais.id' => 'required',
                'provincia.id' => 'required',
                'tipo.categoria.id' => 'required',
                'oficina.id' => 'required',
                'LinkPago.*' => 'el campo link de pago debe tener una URL válida'
            ], $messages
        );


        $v->sometimes('costo', 'required|numeric|min:1', function ($request) {
            return isset($request['tipo']['flujo']) && $request['tipo']['flujo'] == 'CONSTRUCCION';
        });

        $v->sometimes('LinkPago', 'url', function ($request) {
            return isset($request['tipo']['flujo']) && $request['tipo']['flujo'] == 'CONSTRUCCION';
        }, ['LinkPago.*' => 'el campo link de pago debe tener una URL válida']);
//        $v->sometimes('idFormulario', 'required|numeric', function ($request) {
//            return isset($request['tipo']['flujo']) && $request['tipo']['flujo'] == 'CONSTRUCCION';
//        });

//        $v->sometimes('fechaInicioEvaluaciones', 'required|date|after:fechaInicio', function ($request) {
//
//            return isset($request['tipo']['flujo']) && $request['tipo']['flujo'] == 'CONSTRUCCION';
//        });
//        $v->sometimes('fechaFinEvaluaciones', 'required|date|after:fechaInicioEvaluaciones', function ($request) {
//
//            return isset($request['tipo']['flujo']) && $request['tipo']['flujo'] == 'CONSTRUCCION';
//        });
        return $v;
    }


    /**
     * @param $punto PuntoEncuentro
     * @param $actividad Actividad
     * @return mixed
     */
    private function guardarPunto($punto, $actividad)
    {
        if (!isset($punto['idPuntoEncuentro'])) {
            $p = new PuntoEncuentro();
            $p->punto = $punto['punto'];
            $p->horario = $punto['horario'];
            $p->idActividad = $actividad->idActividad;
            $p->idLocalidad = $punto['idLocalidad'];
            $p->idPais = $punto['idPais'];
            $p->idProvincia = $punto['idProvincia'];
            $p->idPersona = $punto['responsable']['idPersona'];
            $p->save();
        }
        return $punto;
    }

    /**
     * @param Request $request
     * @param $actividad
     * @return Boolean
     */
    private function guardarActividad(Request $request, $actividad)
    {
        foreach ($request->except('idActividad',
            'casasPlanificadas',
            'casasConstruidas',
            'comentarios',
            'tipoConstruccion',
            'idListaCTCT',
            'modificado_por.idPersona',
            'idOficina'
        )
                 as $field => $value) {

            $esFecha = in_array($field, $actividad->getDates());

            if (!is_array($value) && Schema::hasColumn('Actividad', $field) && $esFecha) {
                $value = Carbon::parse($value)->format('Y-m-d');
                $actividad->{$field} = $value;
            }

            if (!is_array($value) && Schema::hasColumn('Actividad', $field) && !$esFecha) {
                $actividad->{$field} = $value;
            }

        }

        $actividad->estadoConstruccion = ($request->estadoConstruccion) ? "Abierta" : "Cerrada";
        $actividad->idPais = $request['pais']['id'];
        $actividad->idProvincia = $request['provincia']['id'];
        $actividad->idLocalidad = $request['localidad']['id'];
        $actividad->idCoordinador = $request['coordinador']['idPersona'];
        $actividad->idOficina = $request['oficina']['id'];

        if (empty($request['idUnidadOrganizacional'])) {
            $actividad->idUnidadOrganizacional = UnidadOrganizacional::where('nombre', 'No Aplica')
                ->first()
                ->idUnidadOrganizacional;
        }

            // deberia tomar valor de auth()->user
        $actividad->idPersonaModificacion = auth()->user()->idPersona;


            // Campos definidos en la DB como NOT NULL, sin valor default y que no estan presentes en el $request //
        $actividad->actividadSecundaria = 1;
        $actividad->casasConstruidas = 0;
        $actividad->casasPlanificadas = 0;
        $actividad->comentarios = '';
        $actividad->idEncuestaDinamica = 0;
        $actividad->idListaCTCT = '';
        $actividad->lugar = '';
        $actividad->mostrarFB = 0;
        $actividad->tipoConstruccion = '';
        $actividad->moneda = 'ARS';

        if ($actividad->save()) {
            if (!empty($request->puntos_encuentro)) {
                foreach ($request->puntos_encuentro as $punto) {
                    $punto = $this->guardarPunto($punto, $actividad);
                }
            }

            foreach ($request->puntosEncuentroBorrados as $borrado) {
                $punto = PuntoEncuentro::find($borrado['idPuntoEncuentro']);
                $punto->delete();
            }
            return true;
        }

        return false;
    }

}
