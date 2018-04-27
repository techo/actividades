<?php

namespace App\Http\Controllers\backoffice;

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
        $roles = $roles->collection;
        return view('backoffice.roles.index', compact('roles'));
    }

    public function update(Request $request, $id)
    {
        $user = Persona::findOrFail($id);
        if($request->has('rolID') && Role::find($request->rolID)){
            $user->syncRoles($request->rolID);
            return response("Rol actualizado correctamente", 200);
        }
        return abort(422);
    }
}
