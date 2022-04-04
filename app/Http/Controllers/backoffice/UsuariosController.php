<?php

namespace App\Http\Controllers\backoffice;

use App\Persona;
use App\FichaMedica;
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
        $usuario = Persona::find($id);
        $ficha = FichaMedica::where('idPersona', $id)->first();
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
            'sexo' => $usuario->sexo,
            'nacimiento' => $usuario->fechaNacimiento,
            'telefono' => $usuario->telefonoMovil,
            'pais' => $usuario->pais,
            'provincia' => $usuario->provincia,
            'localidad' => $usuario->localidad,
            'dni' => $usuario->dni,
            'rol' => $rol,
            'email_verified_at' => $usuario->email_verified_at,
        ];

        $edicion = false;
        return view('backoffice.usuarios.show', compact('edicion', 'arrUsuario', 'usuario', 'ficha'));
    }

    public function delete(Persona $id)
    {
        if ($id->delete()){
            Session::flash('mensaje', 'Persona eliminada correctamente');
        } else {
            Session::flash('mensaje', 'Ocurrio un error al querer eliminar a la Persona');
        }
        return redirect()->to('/admin/usuarios');
    }
}
