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
use App\Services\Listados\InscripcionesCatalogo;
use App\Services\Push\PushNotificationService;
use App\Persona;
use App\PuntoEncuentro;
use App\Coordinador;
use App\Http\Requests\CrearCoordinador;
use App\Http\Resources\ActividadResource;
use App\Inscripcion;
use App\Rules\FechaFinActividad;
use App\UnidadOrganizacional;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ActividadesController extends Controller
{
    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

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
        $actividad->idPersonaCreacion = auth()->user()->idPersona;

        $actividad->fechaInicioInscripciones = $validado['fechaInicioInscripciones'];
        $actividad->fechaFinInscripciones = $validado['fechaFinInscripciones'];
        $actividad->fechaInicioEvaluaciones = $validado['fechaInicioEvaluaciones'];
        $actividad->fechaFinEvaluaciones = $validado['fechaFinEvaluaciones'];
        
        $actividad->save();
        $actividad->tipo; //para mostrar categoria

        //por defecto se carga un grupo raíz
        $grupo = new Grupo();
        $grupo->idPadre = 0;
        $grupo->nombre = $actividad->nombreActividad;
        $actividad->grupos()->save($grupo);

        //por defecto se carga con un punto de encuentro igual a la ubicación de la actividad
        $punto = new PuntoEncuentro;
        $punto->punto = $actividad->lugar;
        $punto->horario = $actividad->fechaInicio->format('H:i');
        $punto->idPais = $actividad->idPais;
        $punto->idProvincia = $actividad->idProvincia;
        $punto->idLocalidad = $actividad->idLocalidad;
        $punto->idPersona = auth()->user()->idPersona;
        $actividad->puntosEncuentro()->save($punto);     

        
        $coordinador = new Coordinador();
        //por defecto el usuario cargando es coordinador
        $coordinador->idPersona = auth()->user()->idPersona;
        $actividad->coordinadores()->save($coordinador);
        $actividad->idPersonaCreacion = auth()->user()->idPersona;
        if ($request->has('comunidades_tags')) {
            $this->linkComunidades($actividad, $request->input('comunidades_tags'));
        }
        return response()->json($actividad->fresh());
    }

    public function linkComunidades($actividad, $comunidades){
        $idComunidades = collect($comunidades)->pluck('idComunidad')->toArray();
        $actividad->comunidades()->sync($idComunidades);
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

        if (!empty($validado['linkEvaluacion'])) {
            $validado['linkEvaluacion'] = rtrim(strstr($validado['linkEvaluacion'], '/viewform', true), '/') . '/';
        }
        $actividad->fill($validado);

        $hayCambio = $actividad->isDirty('fechaInicio');

        $actividad->save();

        if ($request->has('comunidades_tags')) {
            $this->linkComunidades($actividad, $request->input('comunidades_tags'));
        }

        if ($hayCambio) {
            $this->enviarPushCambio($actividad);
        }

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
        $id->load('tipo', 'comunidades:actividad_comunidad.idComunidad,nombre');
        return response()->json($id);
    }

    public function eliminarCoordinador(CrearCoordinador $request, Actividad $actividad, Coordinador $coordinador)
    {    
        if ($actividad->idPersonaCreacion != $coordinador->idPersona){
            $coordinador->delete();
            $mensaje = 'ok';
        } else {
            $mensaje = 'error';
        }
        return response()->json($mensaje);
    }

    public function guardarCoordinador(CrearCoordinador $request, Actividad $actividad, Persona $persona)
    {    
        $coordinador = new Coordinador();
        $coordinador->idPersona = $persona->idPersona;
        $actividad->coordinadores()->save($coordinador);
        $persona->nombre = $persona->nombres . ' ' . $persona->apellidoPaterno . ' (' . $persona->mail . ')';
        return $persona;
    }

    public function coordinadores(Actividad $id)
    {    

        $coordinadores = $id->coordinadores;
        // dd($coordinadores[0]->persona);
        foreach ($coordinadores as $coordinador) {
            $coordinador->nombre = $coordinador->persona->nombres . ' ' . $coordinador->persona->apellidoPaterno. ' (' . $coordinador->persona->mail . ')';
        }
        return $coordinadores;
    }

    public function puntos(Actividad $id)
    {
        $actividad = $id;

        $datatablePuntosConfig = config('datatables.puntos');
        $fieldsPuntos = json_encode($datatablePuntosConfig['fields']);
        $sortOrderPuntos = json_encode($datatablePuntosConfig['sortOrder']);

        return view('backoffice.actividades.puntos', 
            compact(
                'actividad',
                'fieldsPuntos',
                'sortOrderPuntos'
            ) 
        );
    }

    public function inscripciones(Actividad $id)
    {
        $actividad = $id;

        // Primer render: fijas + columnas por defecto. La configuración real del
        // usuario la carga el selector de columnas vía GET ajax/listados/.../config.
        $fields = json_encode((new InscripcionesCatalogo)->defaultFields($actividad->idActividad));
        $sortOrder = json_encode(config('datatables.inscripciones.sortOrder'));

        $camposInscripciones = json_encode(config('dropdownOptions.actividad.filtroInscripciones.campos'));
        $condiciones = json_encode(config('dropdownOptions.actividad.filtroInscripciones.condiciones'));

        return view('backoffice.actividades.inscripciones', 
            compact(
                'actividad',
                'fields',
                'sortOrder',
                'camposInscripciones',
                'condiciones'
            ) 
        );
    }

    public function confirmarInscripcion(Actividad $actividad, Inscripcion $inscripcion, $idPersona)
    {
        $fields = json_encode((new InscripcionesCatalogo)->defaultFields($actividad->idActividad));
        $sortOrder = json_encode(config('datatables.inscripciones.sortOrder'));

        $camposInscripciones = json_encode(config('dropdownOptions.actividad.filtroInscripciones.campos'));
        $condiciones = json_encode(config('dropdownOptions.actividad.filtroInscripciones.condiciones'));
        $persona = Persona::findOrFail($idPersona);

        return view('backoffice.actividades.inscripciones', 
            compact(
                'inscripcion',
                'persona',
                'actividad',
                'fields',
                'sortOrder',
                'camposInscripciones',
                'condiciones'
            ) 
        );
    }

    public function grupos(Actividad $id)
    {
        $actividad = $id;

        $fieldsMiembros = json_encode(config('datatables.miembros.fields'));
        $sortOrderMiembros = json_encode(config('datatables.miembros.sortOrder'));
        $miembros = $actividad->miembros;

        return view('backoffice.actividades.grupos', 
            compact(
                'actividad',
                'fieldsMiembros',
                'sortOrderMiembros',
                'miembros'
            ) 
        );
    }

    public function jornadas(Actividad $id)
    {
        $actividad = $id;

        $fieldsJornadas = json_encode(config('datatables.jornadas.fields'));
        $sortOrderJornadas = json_encode(config('datatables.jornadas.sortOrder'));
        $jornadas = $actividad->jornadas;

        return view('backoffice.actividades.jornadas', 
            compact(
                'actividad',
                'fieldsJornadas',
                'sortOrderJornadas',
                'jornadas'
            ) 
        );
    }

    public function evaluaciones(Actividad $id)
    {
        $actividad = $id;

        return view('backoffice.actividades.evaluaciones', 
            compact(
                'actividad'
            ) 
        );
    }

    public function accesos(Actividad $id)
    {
        $actividad = $id;

        return view('backoffice.actividades.accesos',
            compact(
                'actividad'
            )
        );
    }

    public function preguntas(Actividad $actividad)
    {
        return view('backoffice.actividades.preguntas',
            compact(
                'actividad'
            )
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Actividad $id)
    {
        $actividad = $id;
        $edicion = false;
        $compartir = true;

        return view(
            'backoffice.actividades.show',
            compact(
                'actividad',
                'edicion',
                'compartir'
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $actividad = Actividad::findOrFail($id);

        // if ($actividad->fechaFin < Carbon::today()) {
        //     Session::flash('error', 'No se puede eliminar una actividad que ya ha concluido.');
        //     return redirect()->back();
        // }
        if ($actividad->idPais !== auth()->user()->idPaisPermitido){
            Session::flash('error', 'No tiene permisos para eliminar esta actividad.');
            return redirect()->back();
        }

        try {
            $grupos = Grupo::where('idActividad', '=', $actividad->idActividad)->delete();
            $grupo_persona = GrupoRolPersona::where('idActividad', '=', $actividad->idActividad)->delete();
            $this->enviarNotificaciones($actividad);
            $this->enviarPushCambio($actividad);
            $actividad->delete();

        } catch (\Exception $exception) {
            return response($exception->getMessage(), 500);
        }
        Session::flash('mensaje', 'La actividad se eliminó correctamente');
        return redirect()->to('/admin/actividades');
    }



    public function clonar(Request $request, Actividad $id)
    {
        DB::beginTransaction();
        try {
            // Se clona la actividad de la ruta (sobre la que ya autorizó `can:editar`),
            // no un id arbitrario del body.
            $original = $id;
            $clon = $original->replicate();
            $clon->nombreActividad = 'Copia de '. $original->nombreActividad;
            $clon->idPersonaCreacion = auth()->user()->idPersona;
            $clon->push();
            foreach ($original->puntosEncuentro as $punto) {
                $nuevoPunto = $punto->replicate();
                $nuevoPunto->idActividad = $clon->idActividad;
                $nuevoPunto->push();
            }
            foreach ($original->coordinadores as $coordinador) {
                $nuevoCoordinador = $coordinador->replicate();
                $nuevoCoordinador->idActividad = $clon->idActividad;
                $nuevoCoordinador->push();
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
     * Crea grupo Raiz al crear una actividad nueva
     * @param Actividad $actividad
     * @return bool
     */
    private function crearGrupo(Actividad $actividad)
    {
        if ($actividad->idPais !== auth()->user()->idPaisPermitido){
            Session::flash('error', 'No tiene permisos para crear grupos en esta actividad.');
            return redirect()->back();
        }
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

    private function enviarPushCambio(Actividad $actividad)
    {
        $actividad->load('inscripciones.persona.pais', 'inscripciones.persona.dispositivos');
        foreach ($actividad->inscripciones as $inscripcion) {
            $this->pushService->enviarLocalizado(
                $inscripcion->persona,
                'push.cambio_actividad_titulo',
                'push.cambio_actividad_cuerpo',
                ['actividad' => $actividad->nombreActividad],
                ['tipo' => 'actividad', 'estado' => 'CAMBIO', 'idActividad' => $actividad->idActividad]
            );
        }
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
            Log::info('Envío por cancelación actividad ' . $actividad->idActividad . '(' . $actividad->nombreActividad . '): No se encontraron inscripciones para la actividad.');
        }

    }

}
