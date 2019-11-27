<?php

namespace App\Http\Controllers\backoffice;

use App\Oficina;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class OficinasController extends Controller
{
	public function index()
    {
        $datatableConfig = config('datatables.oficinas');
        $fields = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.oficinas.index', compact('fields', 'sortOrder'));
    }

    // public function create()
    // {
    //     $edicion = true;
    //     return view('backoffice.usuarios.create', compact('edicion'));
    // }

    // public function show(Request $request, $id)
    // {
    //     $usuario = Persona::find($id);
    //     $rol = $usuario->roles->toArray();
    //     if(!empty($rol)){
    //         $rol = array_shift($rol);
    //         $rol['rol'] = $rol['name'];
    //     } else {
    //         $rol = null;
    //     }
    //     $arrUsuario = [
    //         'idUsuario' => $usuario->idPersona,
    //         'email' => $usuario->mail,
    //         'nombre' => $usuario->nombres,
    //         'apellido' => $usuario->apellidoPaterno,
    //         'sexo' => $usuario->sexo,
    //         'nacimiento' => $usuario->fechaNacimiento,
    //         'telefono' => $usuario->telefonoMovil,
    //         'pais' => $usuario->pais,
    //         'provincia' => $usuario->provincia,
    //         'localidad' => $usuario->localidad,
    //         'dni' => $usuario->dni,
    //         'rol' => $rol,
    //         'email_verified_at' => $usuario->email_verified_at,
    //     ];

    //     $edicion = false;
    //     return view('backoffice.usuarios.show', compact('edicion', 'arrUsuario', 'usuario'));
    // }

    // public function delete(Persona $id)
    // {
    //     if ($id->delete()){
    //         Session::flash('mensaje', 'Persona eliminada correctamente');
    //     } else {
    //         Session::flash('mensaje', 'Ocurrio un error al querer eliminar a la Persona');
    //     }
    //     return redirect()->to('/admin/usuarios');
    // }

    public function getOficinas()
    {
        return Oficina::all();
    }
}
