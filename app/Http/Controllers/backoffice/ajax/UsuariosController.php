<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Resources\CoordinadorResource;
use App\Http\Resources\RolResource;
use App\Persona;
use App\Search\UsuariosSearch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;

class UsuariosController extends Controller
{
    public function index(Request $request)
    {
        $result = UsuariosSearch::apply($request);
        $usuarios = CoordinadorResource::collection($result); //el nombre del resource no tiene sentido acá
        return response()->json($usuarios);
    }

    public function getRol($id)
    {
        $rol = Persona::find($id)->roles()->first();
        return new RolResource($rol);
    }
}
