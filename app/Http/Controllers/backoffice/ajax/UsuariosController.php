<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Resources\CoordinadorResource;
use App\Http\Resources\RolResource;
use App\Http\Resources\UsuariosResource;
use App\Http\Services\UserService;
use App\Persona;
use App\Search\UsuariosSearch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        $filtro = $request->all();
        if($request->has('filter')){
            $filtro['usuario'] = $request->filter;
        }
        $result = UsuariosSearch::apply($filtro);
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
}
