<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Http\Resources\CoordinadorResource;
use App\Http\Resources\RolResource;
use App\Http\Resources\UsuariosResource;
use App\Http\Services\UserService;
use App\Persona;
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
        $result = UsuariosSearch::apply($filtros);
        $usuarios = CoordinadorResource::collection($result); //el nombre del resource no tiene sentido acá
        return response()->json($usuarios);
    }

    public function index(Request $request)
    {
        $filtros = [];
        if($request->has('usuario')){
            $filtros['usuario'] = $request->usuario;
        }
        
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

        $result = UsuariosSearch::apply($filtros, $sort, $per_page);
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
        $validator = $this->userService->createValidator($request);

        if ($validator->passes()) {
              if ($this->userService->editarUsuario($request)) {
                  return response()->json(['Usuario editado correctamente'], 200);
              }
            return response()->json('Error desconocido', 500);
        }
        return response($validator->errors()->all(), 422);
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
                'estado',
                'presente',
            ])
            ->orderByRaw($sort)
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
                DB::raw("count(comentario) comentario"),
            ])
            ->groupBy('Actividad.nombreActividad', 'Tipo.nombre', 'Actividad.fechaInicio')
            ->orderByRaw($sort)
            ->paginate();
    }
}
