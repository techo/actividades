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
        $actor = auth()->user();

        if (!$request->has('rolID') || !($rol = Role::find($request->rolID))) {
            return abort(422);
        }

        // Solo un admin puede otorgar el rol admin (evita elevación de privilegios
        // si en el futuro se delega `asignar_roles` a un rol menor).
        if ($rol->name === 'admin' && !$actor->hasRole('admin')) {
            abort(403, 'No autorizado para asignar el rol admin.');
        }

        // Salvo admin, solo se pueden asignar roles a usuarios del país permitido.
        abort_unless($actor->hasRole('admin') || $user->idPais == $actor->idPaisPermitido, 403);

        $user->syncRoles($rol->id);
        return response("Rol actualizado correctamente", 200);
    }
}
