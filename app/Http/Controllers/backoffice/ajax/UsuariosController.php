<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Resources\CoordinadorResource;
use App\Http\Resources\RolResource;
use App\Http\Resources\UsuariosResource;
use App\Persona;
use App\Search\UsuariosSearch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;

class UsuariosController extends Controller
{
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
}
