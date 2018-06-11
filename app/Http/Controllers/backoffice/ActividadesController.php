<?php

namespace App\Http\Controllers\backoffice;

use App\Actividad;
use App\CategoriaActividad;
use App\Grupo;
use App\Rules\FechaFinActividad;
use Carbon\Carbon;
use App\Pais;
use App\PuntoEncuentro;
use App\UnidadOrganizacional;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use App\Rules\PuntoEncuentro as PuntoEncuentroRule;

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
            if ($this->guardarActividad($request, $actividad) && $this->crearGrupo($actividad)) {
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
        $compartir = true;
        $paises = Pais::all();
        $actividad = Actividad::with(
            [
                'tipo.categoria',
                'oficina',
                'modificadoPor',
                'puntosEncuentro.pais',
                'puntosEncuentro.provincia',
                'puntosEncuentro.localidad',
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

            $datatableConfig = config('datatables.inscripciones');
            $fields = $datatableConfig['fields'];
            if ($actividad->costo > 0) {
                $checkPago = [[
                    'name' => '__component:pago',
                    'title' => 'Pago',
                    'titleClass' => 'text-center',
                    'dataClass' => 'text-center'
                ]];
                array_splice($fields, count($fields) - 2, 0, $checkPago);

            }
            $fields = json_encode($fields);
            $sortOrder = json_encode($datatableConfig['sortOrder']);

            $camposInscripciones = config('dropdownOptions.actividad.filtroInscripciones.campos');
            $condiciones = config('dropdownOptions.actividad.filtroInscripciones.condiciones');;

            $camposInscripciones = json_encode($camposInscripciones);
            $condiciones = json_encode($condiciones);

            $datatableMiembrosConfig = config('datatables.miembros');
            $fieldsMiembros = json_encode($datatableMiembrosConfig['fields']);
            $sortOrderMiembros = json_encode($datatableMiembrosConfig['sortOrder']);
            $miembros = $actividad->miembros;

            return view(
                'backoffice.actividades.show',
                compact(
                    'actividad',
                    'paises',
                    'provincias',
                    'localidades',
                    'edicion',
                    'tipos',
                    'categorias',
                    'compartir',
                    'fields',
                    'sortOrder',
                    'fieldsMiembros',
                    'sortOrderMiembros',
                    'miembros'
                    'sortOrder',
                    'camposInscripciones',
                    'condiciones'
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
            'fechaFinInscripciones.after_or_equal' =>
                'La fecha de fin de las inscripciones debe ser posterior a la fecha de inicio de las inscripciones',
            'fechaFinInscripciones.before_or_equal' =>
                'Las inscripciones deben finalizar antes del inicio de la actividad',
            'inscripcionesInternas' =>
                'visibilidad de las inscripciones',
            'fechaInicioInscripciones.before_or_equal' =>
                'La fecha de inicio de las inscripciones debe ser anterior al inicio de la actividad',
            'limiteInscripciones.*' =>
                'El límite máximo de voluntarios debe tener un valor numérico válido',
            'coordinador.*' =>
                'Debe seleccionar un Coordinador para la actividad',
            'idTipo.*' =>
                'Debe seleccionar una categoría y un tipo de actividad',
            'tipo.*' =>
                'Debe seleccionar una categoría y un tipo de actividad',
            'localidad.*' =>
                'El campo localidad debe tener un valor válido',
            'oficina.*' =>
                'El campo Oficina es requerido',
            'provincia.*' =>
                'Debe seleccionar la provincia de la actividad',
            'nombreActividad.*' =>
                'La actividad debe tener un nombre',
            'pais.*' =>
                'Debe seleccionar el país de la actividad',
            'costo.*' =>
                'Debe especificar el costo de participar en la construcción',
        ];
        $v = Validator::make(
            $request->all(),
            [
                'coordinador.idPersona' => 'required | numeric',
                'descripcion' => 'required',
                'fechaFin' => ['required', 'date', new FechaFinActividad($request->fechaInicio)],
                'fechaInicio' => 'required | date',
                'fechaInicioInscripciones' => 'required | date | before_or_equal:fechaInicio',
                'fechaFinInscripciones' => ['required', 'date', new FechaFinActividad($request->fechaInicioInscripciones), 'before_or_equal:fechaInicio'],
                'idTipo' => 'required',
                'inscripcionInterna' => 'required',
                'limiteInscripciones' => 'numeric',
                'localidad.id' => 'required',
                'mensajeInscripcion' => 'required',
                'nombreActividad' => 'required',
                'oficina.id' => 'required',
                'pais.id' => 'required',
                'provincia.id' => 'required',
                'puntos_encuentro' => [new PuntoEncuentroRule],
                'tipo.categoria.id' => 'required',
            ], $messages
        );

        $v->sometimes('costo', 'required|numeric|min:1', function ($request) {
            return isset($request['tipo']['flujo']) && $request['tipo']['flujo'] == 'CONSTRUCCION';
        });

        $v->sometimes('LinkPago', 'url', function ($request) {
            return isset($request['tipo']['flujo']) && $request['tipo']['flujo'] == 'CONSTRUCCION';
        }, ['LinkPago.*' => 'el campo link de pago debe tener una URL válida']);

        return $v;
    }


    /**
     * @param $punto PuntoEncuentro
     * @param $actividad Actividad
     * @return mixed
     */
    private function guardarPunto($punto, $actividad)
    {
        $p = new PuntoEncuentro();
        $p->punto = $punto['punto'];
        $p->horario = $punto['horario'];
        $p->idActividad = $actividad->idActividad;
        $p->idLocalidad = $punto['idLocalidad'];
        $p->idPais = $punto['idPais'];
        $p->idProvincia = $punto['idProvincia'];
        $p->idPersona = $punto['responsable']['idPersona'];
        $p->save();
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
            'idOficina',
            'fechaInicio',
            'fechaFin',
            'fechaInicioInscripciones',
            'fechaFinInscripciones'
        ) as $field => $value) {

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
        $actividad->fechaInicio = $request->fechaInicio;
        $actividad->fechaFin = $request->fechaFin;
        $actividad->fechaInicioInscripciones = $request->fechaInicioInscripciones;
        $actividad->fechaFinInscripciones = $request->fechaFinInscripciones;

        if (empty($request['idUnidadOrganizacional'])) {
            $actividad->idUnidadOrganizacional = UnidadOrganizacional::where('nombre', 'No Aplica')
                ->first()
                ->idUnidadOrganizacional;
        }

        if ($request->is('admin/actividades/crear')) {
            $actividad->idPersonaCreacion = auth()->user()->idPersona;
        }

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
                $puntosGuardados = $actividad->puntosEncuentro->count() > 0 ? $actividad->puntosEncuentro->pluck('idPuntoEncuentro')->toArray() : [];
                foreach ($request->puntos_encuentro as $punto) {
                    if (!empty($punto['nuevo']) && $punto['nuevo'] == true) {
                        $punto = $this->guardarPunto($punto, $actividad);
                    }
                }
            }

            foreach ($request->puntosEncuentroBorrados as $borrado) {
                $punto = PuntoEncuentro::find($borrado['idPuntoEncuentro']);
                if ($punto) {
                    $punto->delete();
                }
            }
            return true;
        }

        return false;
    }

    private function crearGrupo(Actividad $actividad)
    {
        $grupo = new Grupo();
        $grupo->idActividad = $actividad->idActividad;
        $grupo->idPadre = 0;
        $grupo->nombre = $actividad->nombre;
        if ($grupo->save()) {
            return true;
        }
        return false;
    }
}
