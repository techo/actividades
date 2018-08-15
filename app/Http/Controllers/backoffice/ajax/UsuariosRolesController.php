<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\Http\Resources\RolResource;
use App\Persona;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UsuariosRolesController extends Controller
{
    public function index()
    {
        $roles = RolResource::collection(Role::all());
        return response()->json($roles, 200);
    }
}
