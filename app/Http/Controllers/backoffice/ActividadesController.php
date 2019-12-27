<?php

namespace App\Http\Controllers\backoffice;

use App\Actividad;
use App\CategoriaActividad;
use App\Grupo;
use App\GrupoRolPersona;
use App\Http\Controllers\Controller;
use App\Http\Requests\CrearActividad;
use App\Jobs\EnviarMailsCancelacionActividad;
use App\Pais;
use App\Persona;
use App\PuntoEncuentro;
use App\Rules\FechaFinActividad;
use App\Rules\PuntoEncuentro as PuntoEncuentroRule;
use App\UnidadOrganizacional;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
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
        return view('backoffice.actividades.index', compact('fields', 'sortOrder'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $edicion = true;
        $paises = Pais::has("provincias")->get();
        $columns = Schema::getColumnListing('Actividad');
        $arrayColumnas = array_fill_keys($columns, null);


        $actividad = json_encode($arrayColumnas);
        $categorias = CategoriaActividad::with('tipos')->get();
        $tipos = $categorias->first()->tipos;
        $categorias = json_encode($categorias);

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
    public function store(CrearActividad $request)
    {
        $actividad = new Actividad();
        
        $validado = $request->validated();

        $actividad->fill($validado);
        
        //por defecto el usuario cargando es coordinador
        $actividad->idCoordinador = auth()->user()->idPersona;

        //por defecto las fechas de inscripciones/evaluaciones son 10 días antes/después;
        if($request->filled('fechaInicioInscripciones') && $request->has('fechaInicioInscripciones')) {
            $actividad->fechaInicioInscripciones = $validado['fechaInicioInscripciones'];
            $actividad->fechaFinInscripciones = $validado['fechaFinInscripciones'];
        }
        else {
            $actividad->fechaInicioInscripciones = $actividad->fechaInicio->subDays(10);
            $actividad->fechaFinInscripciones = $actividad->fechaInicio->subMinutes(1);
        }

        if($request->filled('fechaInicioEvaluaciones') && $request->has('fechaInicioEvaluaciones')) {
            $actividad->fechaInicioEvaluaciones = $validado['fechaInicioEvaluaciones'];
            $actividad->fechaFinEvaluaciones = $validado['fechaFinEvaluaciones'];
        }
        else{
            $actividad->fechaInicioEvaluaciones = $actividad->fechaFin->addMinutes(1);
            $actividad->fechaFinEvaluaciones = $actividad->fechaFin->addDays(10);
        }

        $actividad->save();
        $actividad->tipo; //para mostrar categoria

        //por defecto se carga con un punto de encuentro igual a la ubicación de la actividad
        $punto = new PuntoEncuentro;
        $punto->punto = $actividad->lugar;
        $punto->horario = $actividad->fechaInicio->format('H:i');
        $punto->idPais = $actividad->idPais;
        $punto->idProvincia = $actividad->idProvincia;
        $punto->idLocalidad = $actividad->idLocalidad;
        $punto->idPersona = $actividad->idCoordinador;
        $actividad->puntosEncuentro()->save($punto);        

        return response()->json($actividad->fresh());

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CrearActividad $request, Actividad $actividad)
    {
        $validado = $validado = $request->validated();
        
        $validado['lugar'] = (!$validado['lugar'])?"":$validado['lugar'];

        $actividad->fill($validado);
        $actividad->save();

        $actividad->tipo;

        return response()->json($actividad);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function actividad(Actividad $id)
    {
        $id->tipo;
        return response()->json($id);
    }

    public function guardarCoordinador(Actividad $actividad, Persona $persona)
    {    
        $actividad->idCoordinador = $persona->idPersona;
        $actividad->save();
        $persona->nombre = $persona->nombres . ' ' . $persona->apellidoPaterno . ' (' . $persona->mail . ')';
        return $persona;
    }

    public function coordinador(Actividad $id)
    {    
        $persona = $id->coordinador;
        $persona->nombre = $persona->nombres . ' ' . $persona->apellidoPaterno . ' (' . $persona->mail . ')';
        return $persona;
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
        $paises = Pais::has("provincias")->get();

        $actividad = Actividad::with(
            [
                'tipo.categoria',
                'oficina',
                'modificadoPor' =>function($query){ $query->select('idPersona','nombres','apellidoPaterno','dni'); },
                'puntosEncuentro.pais',
                'puntosEncuentro.provincia',
                'puntosEncuentro.localidad',
                'puntosEncuentro.responsable' =>function($query){ $query->select('idPersona','nombres','apellidoPaterno','dni'); },
                'pais',
                'provincia',
                'localidad',
                'coordinador' =>function($query){ $query->select('idPersona','nombres','apellidoPaterno','dni'); }
            ]
        )
        ->where('idActividad', $id)->first();

        if ($actividad) {
            if ($actividad->coordinador) {
                $actividad->coordinador->nombre = $actividad->coordinador->nombreCompleto;
            }

            $categorias = CategoriaActividad::all();

            try {
                $tipos = $actividad->tipo->categoria->tipos;
            } catch (\Exception $e) {
                $actividad->tipo->categoria = null;
                $tipos = null;
            }

            try {
                $provincias = $actividad->pais->provincias;
                $localidades = $actividad->provincia->localidades;

            } catch (\Exception $e) {
                $provincias = null;
                $localidades = null;
            }

            $datatableConfig = config('datatables.inscripciones');
            $fields = $datatableConfig['fields'];

            if ($actividad->confirmacion == 1) {
                $checkConfirma = [[
                    'name' => '__component:confirma',
                    'title' => 'Confirma',
                    'titleClass' => 'text-center',
                    'dataClass' => 'text-center'
                ]];
                array_splice($fields, count($fields) - 1, 0, $checkConfirma);

            }
            if ($actividad->pago == 1) {
                $checkPago = [[
                    'name' => '__component:pago',
                    'title' => 'Pago',
                    'titleClass' => 'text-center',
                    'dataClass' => 'text-center'
                ]];
                array_splice($fields, count($fields) - 1, 0, $checkPago);

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

            $datatablePuntosConfig = config('datatables.puntos');
            $fieldsPuntos = json_encode($datatablePuntosConfig['fields']);
            $sortOrderPuntos = json_encode($datatablePuntosConfig['sortOrder']);

            foreach($actividad->puntosEncuentro as &$punto) {
                if($punto->tieneInscriptos()) {
                    $punto->borrable = false;
                } else {
                    $punto->borrable = true;
                }
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
                    'categorias',
                    'compartir',
                    'fields',
                    'sortOrder',
                    'fieldsMiembros',
                    'sortOrderMiembros',
                    'miembros',
                    'camposInscripciones',
                    'condiciones',
                    'fieldsPuntos',
                    'sortOrderPuntos'
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $actividad = Actividad::findOrFail($id);

        if ($actividad->fechaFin < Carbon::today()) {
            Session::flash('error', 'No se puede eliminar una actividad que ya ha concluido.');
            return redirect()->back();
        }


        try {
            $grupos = Grupo::where('idActividad', '=', $actividad->idActividad)->delete();
            $grupo_persona = GrupoRolPersona::where('idActividad', '=', $actividad->idActividad)->delete();
            $this->enviarNotificaciones($actividad);
            $actividad->delete();

        } catch (\Exception $exception) {
            return response($exception->getMessage(), 500);
        }
        Session::flash('mensaje', 'La actividad se eliminó correctamente');
        return redirect()->to('/admin/actividades/usuario');
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
            'oficina.*' =>
                'El campo Oficina es requerido',
            'provincia.*' =>
                'Debe seleccionar la provincia de la actividad',
            'nombreActividad.*' =>
                'La actividad debe tener un nombre',
            'pais.*' =>
                'Debe seleccionar el país de la actividad',
            'montoMin.*' =>
                'Debe especificar el monto mínimo de donación',
            'fechaInicioEvaluaciones.after_or_equal' => 'La fecha de inicio de las evaluaciones debe ser igual o
                posterior al final de la actividad',
            'beca.url' => 'El enlace al formulario de solicitud de beca debe ser una URL válida'
        ];
        $v = Validator::make(
            $request->all(),
            [
                'nombreActividad'           => 'required',
                'coordinador.idPersona'     => 'required | numeric',
                'tipo.categoria.id'         => 'required',
                'idTipo'                    => 'required',
                'oficina.id'                => 'required',
                'fechaInicio'               => 'required | date',
                'fechaFin'                  => ['required', 'date', new FechaFinActividad($request->fechaInicio)], 
                'fechaInicioInscripciones'  => 'required | date | before_or_equal:fechaInicio',
                'fechaFinInscripciones'     => ['required', 'date', new FechaFinActividad($request->fechaInicioInscripciones), 'before_or_equal:fechaInicio'],
                'fechaInicioEvaluaciones'   => 'required | date | after_or_equal:fechaFin',
                'fechaFinEvaluaciones'      => ['required', 'date', new FechaFinActividad($request->fechaInicioEvaluaciones), 'after_or_equal:fechaInicioEvaluaciones'],
                'descripcion'               => 'required',
                'pais.id'                   => 'required',
                'provincia.id'              => 'required',
                'puntos_encuentro'          => [new PuntoEncuentroRule],
                'limiteInscripciones'       => 'numeric',                
                'inscripcionInterna'        => 'required',
                'mensajeInscripcion'        => 'required',
            ], $messages
        );

        $v->sometimes('montoMin', 'required|numeric|min:1', function ($request) {
            return $request->filled('pago') && $request->pago == 1;
        });

        if($request->filled('beca')){
            $v->sometimes('beca', 'url', function ($request) {
                return $request->filled('pago') && $request->pago == 1;
            });
        }

        return $v;
    }

    public function clonar(Request $request)
    {
        DB::beginTransaction();
        try {
            $original = Actividad::find($request->idActividad);
            $clon = $original->replicate();
            $clon->nombreActividad = 'Copia de '. $original->nombreActividad;
            $clon->push();
            foreach ($original->puntosEncuentro as $punto) {
                $nuevoPunto = $punto->replicate();
                $nuevoPunto->idActividad = $clon->idActividad;
                $nuevoPunto->push();
            }

            $grupoRaizOriginal = Grupo::where('idActividad', $original->idActividad)
                ->where('idPadre', 0)
                ->first();
            $this->clonarGrupo($grupoRaizOriginal, $clon);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response($exception->getMessage(), 500);
        }
        DB::commit();
        return $clon;
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
        $p->estado = $punto['estado'];
        $p->save();
        return $punto;
    }

    private function editarPunto($punto, $editado)
    {
        $punto->punto = $editado['punto'];
        $punto->horario = $editado['horario'];
        $punto->idLocalidad = $editado['idLocalidad'];
        $punto->idPais = $editado['idPais'];
        $punto->idProvincia = $editado['idProvincia'];
        $punto->idPersona = $editado['responsable']['idPersona'];
        $punto->estado = $editado['estado'];
        $punto->save();
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
            'fechaFinInscripciones',
            'fechaInicioEvaluaciones',
            'fechaFinEvaluaciones',
            'fechaLimitePago'
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
        $actividad->idLocalidad = (isset($request['localidad']['id']))?$request['localidad']['id']:null;
        $actividad->idCoordinador = $request['coordinador']['idPersona'];
        $actividad->idOficina = $request['oficina']['id'];
        $actividad->fechaInicio = $request->fechaInicio;
        $actividad->fechaFin = $request->fechaFin;
        $actividad->montoMin = $request->montoMin > 0 ? $request->montoMin : 0;
        $actividad->montoMax = $request->montoMax > 0 ? $request->montoMax : 0;
        $actividad->fechaInicioInscripciones = $request->fechaInicioInscripciones;
        $actividad->fechaFinInscripciones = $request->fechaFinInscripciones;
        $actividad->fechaInicioEvaluaciones = $request->fechaInicioEvaluaciones;
        $actividad->fechaFinEvaluaciones = $request->fechaFinEvaluaciones;
        
        $actividad->fechaLimitePago = $request->fechaLimitePago;

        if (empty($request['idUnidadOrganizacional'])) {
            $actividad->idUnidadOrganizacional = UnidadOrganizacional::where('nombre', 'No Aplica')
                ->first()
                ->idUnidadOrganizacional;
        }

        if ($request->is('admin/actividades/crear')) {
            $actividad->idPersonaCreacion = auth()->user()->idPersona;
        }

        $actividad->idPersonaModificacion = auth()->user()->idPersona;

        $actividad->lugar = '';
        /*Esto debería salir de la configuración*/
        $actividad->moneda = 'ARS';

        if ($actividad->save()) {
            $grupoRaiz = Grupo::where('idActividad', '=', $actividad->idActividad)
                ->where('idPadre', '=', 0)->first();
            if ($grupoRaiz) {
                $grupoRaiz->nombre = $actividad->nombreActividad;
                $grupoRaiz->save();
            }
            //dd($request->puntos_encuentro);

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
                if ($punto && !$punto->tieneInscriptos()) {
                    $punto->delete();
                }
            }

            foreach ($request->puntosEncuentroEditados as $editado) {
                $punto = PuntoEncuentro::find($editado['idPuntoEncuentro']);
                if ($punto) {
                    $this->editarPunto($punto, $editado, $actividad);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Crea grupo Raiz al crear una actividad nueva
     * @param Actividad $actividad
     * @return bool
     */
    private function crearGrupo(Actividad $actividad)
    {
        $grupo = new Grupo();
        $grupo->idActividad = $actividad->idActividad;
        $grupo->idPadre = 0;
        $grupo->nombre = $actividad->nombreActividad;
        if ($grupo->save()) {
            return true;
        }
        return false;
    }

    /**
     * Recursividad para clonar subgrupos
     * @param Grupo $grupoOriginal
     * @param Actividad $actividad
     * @param int $idPadre
     */
    private function clonarGrupo(Grupo $grupoOriginal, Actividad $actividad, $idPadre = 0)
    {

        $nuevoGrupo = Grupo::create([
            'nombre'    => $grupoOriginal->nombre,
            'idPadre'   => $idPadre,
            'idActividad'   => $actividad->idActividad
        ]);
        foreach ($grupoOriginal->grupos as $grupo) {
            $this->clonarGrupo($grupo, $actividad, $nuevoGrupo->idGrupo);
        }
        return;
    }

    private function enviarNotificaciones(Actividad $actividad)
    {
        try{
            foreach ($actividad->inscripciones_validas() as $inscripcion) {
                //visto como hacer acá https://medium.com/@DarkGhostHunter/laravel-3-ways-of-processing-a-job-for-a-deleted-model-56413a512688
                $persona = $inscripcion->persona->toArray();
                $actividad = $inscripcion->actividad->toArray();
                $pais = $inscripcion->actividad->pais->toArray();
                $job = (new EnviarMailsCancelacionActividad($persona, $actividad, $pais));
                dispatch($job);
            };
        } catch (ModelNotFoundException $e){
            \Log::info('Envío por cancelación actividad ' . $actividad->idActividad . '(' . $actividad->nombreActividad . '): No se encontraron inscripciones para la actividad.');
        }

    }

}
