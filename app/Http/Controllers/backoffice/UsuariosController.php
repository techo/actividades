<?php

namespace App\Http\Controllers\backoffice;

use App\Estudios;
use App\Persona;
use App\FichaMedica;
use App\Scopes\BelongsToCountryScope;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;


class UsuariosController extends Controller
{
    public function index()
    {
        $datatableConfig = config('datatables.usuarios');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.usuarios.index', compact('fields', 'sortOrder'));
    }

    public function create()
    {
        $edicion = true;
        return view('backoffice.usuarios.create', compact('edicion'));
    }

    public function show(Request $request, $id)
    {
        // Sin scope de país: para poder abrir el perfil de una persona rescatada de otro
        // país (típicamente quien agarró el país por defecto). El permiso lo decide
        // gestionableCrossPais(): si pertenece a otra coordinación real, se bloquea.
        // withTrashed(): permite ABRIR la ficha de una persona dada de baja (soft-delete)
        // para poder restaurarla (la vista muestra un banner + botón "Restaurar").
        $usuario = Persona::withoutGlobalScope(BelongsToCountryScope::class)->withTrashed()->find($id);
        if (!$usuario) {
            abort(404);
        }
        $borrado = $usuario->trashed();
        if (!$usuario->gestionableCrossPais()){
            Session::flash('error', 'Esta persona pertenece a la coordinación de otro país. Pedí a esa coordinación (o a un administrador global) que la gestione.');
            return redirect()->back();
        }
        $ficha = FichaMedica::where('idPersona', $id)->first();
        $estudios = Estudios::where('idPersona', $id)->first();
        $rol = $usuario->roles->toArray();
        if(!empty($rol)){
            $rol = array_shift($rol);
            $rol['rol'] = $rol['name'];
        } else {
            $rol = null;
        }
        $arrUsuario = [
            'idUsuario' => $usuario->idPersona,
            'email' => $usuario->mail,
            'nombre' => $usuario->nombres,
            'apellido' => $usuario->apellidoPaterno,
            'genero' => $usuario->genero,
            'nacimiento' => $usuario->fechaNacimiento,
            'telefono' => $usuario->telefonoMovil,
            'pais' => $usuario->pais,
            'provincia' => $usuario->provincia,
            'localidad' => $usuario->localidad,
            'dni' => $usuario->dni,
            'rol' => $rol,
            'email_verified_at' => $usuario->email_verified_at,
            'canal_contacto' => $usuario->canal_contacto,
            'estadoPersona' => $usuario->estadoPersona,
            'photo' => $usuario->photo,
            'instagram' => $usuario->instagram,
        ];

        $edicion = false;
        return view('backoffice.usuarios.show', compact('edicion', 'arrUsuario', 'usuario', 'ficha', 'estudios', 'borrado'));
    }

    public function delete($id)
    {
        $persona = Persona::withoutGlobalScope(BelongsToCountryScope::class)->findOrFail($id);

        if (!$persona->gestionableCrossPais()) {
            Session::flash('mensaje', 'No tenés permisos para eliminar a esta persona: pertenece a la coordinación de otro país.');
            return redirect()->to('/admin/usuarios');
        }

        if ($persona->delete()){
            Session::flash('mensaje', 'Persona eliminada correctamente');
        } else {
            Session::flash('mensaje', 'Ocurrio un error al querer eliminar a la Persona');
        }
        return redirect()->to('/admin/usuarios');
    }

    /**
     * Restaura una persona dada de baja (soft-delete). Su mail deja de estar "ocupado"
     * por la fila borrada y vuelve a aparecer en las búsquedas. Contraparte de delete().
     */
    public function restore($id)
    {
        // onlyTrashed: solo aplica a personas efectivamente borradas (findOrFail 404 si no).
        $persona = Persona::withoutGlobalScope(BelongsToCountryScope::class)->onlyTrashed()->findOrFail($id);

        if (!$persona->gestionableCrossPais()) {
            Session::flash('mensaje', 'No tenés permisos para restaurar a esta persona: pertenece a la coordinación de otro país.');
            return redirect()->to('/admin/usuarios');
        }

        $persona->restore();
        Session::flash('mensaje', 'Persona restaurada correctamente');
        return redirect()->to('/admin/usuarios/' . $persona->idPersona);
    }

    public function suscriptos()
    {
        $datatableConfig = config('datatables.suscriptos');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.usuarios.suscriptos', compact('fields', 'sortOrder'));
    }
}
