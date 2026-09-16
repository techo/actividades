<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Http\Resources\CoordinadorResource;
use App\Http\Resources\RolResource;
use App\Http\Resources\UsuariosResource;
use App\Auditoria;
use App\Http\Services\UserService;
use App\Persona;
use App\Scopes\BelongsToCountryScope;
use App\Search\UsuariosSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuariosController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function usuariosSearch(Request $request)
    {
        $filtros = $request->all();
        $result = UsuariosSearch::apply($filtros, 'idPersona desc', 25, $this->esBusquedaCrossPais($request));
        $usuarios = CoordinadorResource::collection($result); //el nombre del resource no tiene sentido acá
        return response()->json($usuarios);
    }

    /**
     * ¿La búsqueda debe ir a toda la base ignorando el país? Solo cuando el término es
     * un email exacto: sirve para rescatar personas registradas en otro país (típico
     * de quienes agarraron el país por defecto). El resto de las búsquedas (por nombre)
     * siguen acotadas al país, para no exponer PII de otros países.
     */
    private function esBusquedaCrossPais(Request $request): bool
    {
        return $request->filled('usuario')
            && filter_var(trim($request->usuario), FILTER_VALIDATE_EMAIL) !== false;
    }

    public function index(Request $request)
    {
        $filtros = [];
        if($request->has('usuario')){
            $filtros['usuario'] = $request->usuario;
        }

        $sort = 'idPersona desc';
        if($request->filled('sort')) {
            if(strpos($request->sort, "|"))
                $sort = join(" ",explode("|", $request->sort));
            else
                $sort = $request->sort;
        }

        $per_page = 25;
        if($request->filled('per_page')) {
            $per_page = $request->per_page;
        }

        $result = UsuariosSearch::apply($filtros, $sort, $per_page, $this->esBusquedaCrossPais($request));
        $usuarios = UsuariosResource::collection($result); // Yo se que es horrible pero no funciona sin esto
        return response()->json($result);
    }

    public function getRol($id)
    {
        $rol = Persona::find($id)->roles()->first();
        return new RolResource($rol);
    }

    public function store(Request $request) {
        $validator = $this->userService->createValidator($request);

        if ($validator->passes()) {
              if ($this->userService->crearUsuario($request)) {
                  return response()->json(['Usuario registrado correctamente'], 200);
              }
            return response()->json('Error desconocido', 500);
        }
        return response($validator->errors()->all(), 422);
    }

    public function update(Request $request) {
        // Sin el scope de país: una persona rescatada de otro país no sería visible con
        // el scope activo (daría 404). El permiso lo decide gestionableCrossPais().
        $persona = Persona::withoutGlobalScope(BelongsToCountryScope::class)
            ->findOrFail($request->idUsuario);

        if (!$persona->gestionableCrossPais()) {
            return response()->json(
                ['No tenés permisos para editar a esta persona: pertenece a la coordinación de otro país.'],
                403
            );
        }

        $validator = $this->userService->createValidator($request);

        if ($validator->passes()) {
              // Dejar rastro cuando es un rescate cross-país (país distinto al del admin).
              if ($this->esAccionCrossPais($persona)) {
                  Auditoria::crear($persona);
              }
              if ($this->userService->editarUsuario($request)) {
                  return response()->json(['Usuario editado correctamente'], 200);
              }
            return response()->json('Error desconocido', 500);
        }
        return response($validator->errors()->all(), 422);
    }

    public function fusionar($persona, Request $request)
    {
        // Sin scope de país en ambas cuentas: el rescate típico fusiona una cuenta
        // varada (otro país) contra la real. El permiso se valida abajo.
        $survivor = Persona::withoutGlobalScope(BelongsToCountryScope::class)->findOrFail($persona);

        $messages = [
            'idPersona.not_in' => 'No se puede fusionar una cuenta consigo misma',
        ];

        $validado = $request->validate([
            'idPersona' => 'required|exists:Persona|not_in:' . $survivor->idPersona,
        ], $messages);

        $target = Persona::withoutGlobalScope(BelongsToCountryScope::class)->findOrFail($validado['idPersona']);

        if (!$survivor->gestionableCrossPais() || !$target->gestionableCrossPais()) {
            return response()->json(
                ['No tenés permisos para fusionar: alguna de las cuentas pertenece a la coordinación de otro país.'],
                403
            );
        }

        if ($this->esAccionCrossPais($survivor) || $this->esAccionCrossPais($target)) {
            Auditoria::crear($target);
        }

        $survivor->fusionar($target);

        return response('ok', 200);
    }

    /**
     * ¿La acción sobre esta persona cruza el país del admin? (para auditar rescates).
     */
    private function esAccionCrossPais(Persona $persona): bool
    {
        $auth = auth()->user();
        return !$auth->esGlobalPais()
            && !in_array((int) $persona->idPais, $auth->paisesPermitidosIds(), true);
    }

    public function inscripciones($persona, Request $request)
    {
        $sort = 'fechaInscripcion desc';
        if($request->filled('sort')) {
            if(strpos($request->sort, "|"))
                $sort = join(" ",explode("|", $request->sort));
            else
                $sort = $request->sort;
        }

        return \App\Inscripcion::where('idPersona', '=', $persona)
            ->join('Actividad', 'Actividad.idActividad', '=', 'Inscripcion.idActividad')
            ->join('Tipo', 'Actividad.idTipo', '=', 'Tipo.idTipo')
            ->select([
                'Actividad.nombreActividad',
                'Tipo.nombre',
                'fechaInscripcion', 
                'rol',
                'presente',
            ])
            ->orderByRaw(\App\Search\SortSanitizer::sanitize($sort, 'fechaInscripcion desc'))
            ->paginate();
    }

    public function evaluaciones($persona, Request $request)
    {
        //orden de la consulta
        $sort = 'Actividad.fechaInicio desc';
        if($request->filled('sort')) {
            if(strpos($request->sort, "|"))
                $sort = join(" ",explode("|", $request->sort));
            else
                $sort = $request->sort;
        }

        return \App\EvaluacionPersona::where('idEvaluado', '=', $persona)
            ->join('Actividad', 'Actividad.idActividad', '=', 'EvaluacionPersona.idActividad')
            ->join('Tipo', 'Actividad.idTipo', '=', 'Tipo.idTipo')
            ->select([
                "Actividad.nombreActividad",
                "Tipo.nombre",
                "Actividad.fechaInicio",
                DB::raw("avg(puntajeSocial) puntajeSocial"),
                DB::raw("avg(puntajeTecnico) puntajeTecnico"),
                DB::raw("avg(puntajeGenero) puntajeGenero"),
                DB::raw("count(comentario) comentario"),
            ])
            ->groupBy('Actividad.nombreActividad', 'Tipo.nombre', 'Actividad.fechaInicio')
            ->orderByRaw(\App\Search\SortSanitizer::sanitize($sort, 'Actividad.fechaInicio desc'))
            ->paginate();
    }

    public function estudios($persona, Request $request)
    {
        //orden de la consulta
        $sort = 'estudios.disciplina_academica desc';
        if($request->filled('sort')) {
            if(strpos($request->sort, "|"))
                $sort = join(" ",explode("|", $request->sort));
            else
                $sort = $request->sort;
        }

        return \App\Estudios::where('idPersona', '=', $persona)
            ->orderByRaw(\App\Search\SortSanitizer::sanitize($sort, 'estudios.disciplina_academica desc'))
            ->paginate();
    }
}
