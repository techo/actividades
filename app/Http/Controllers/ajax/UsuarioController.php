<?php

namespace App\Http\Controllers\ajax;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Persona;
class UsuarioController extends Controller
{
    public function create(Request $request) {
 		$validatedData = $request->validate([
 			'email' => 'required|unique:Persona,mail|email',
        	'nombre' => 'required',
        	'apellido' => 'required',
        	'nacimiento' => 'required',
        	'sexo' => 'required',
        	'telefono' => 'required'
    	]);
    	$fechaNacimiento = new Carbon($request->nacimiento);
    	$persona = new Persona();
        $persona->dni   = $request->dni;
        $persona->mail  = $request->email;
        $persona->sexo  = $request->sexo;
        $persona->idPais    = $request->pais;
        $persona->nombres   = $request->nombre;
        $persona->carrera   = '';
        $persona->idCiudad  = 0;
        $persona->password  = Hash::make($request->pass);
        $persona->lenguaje  = '';
        $persona->pasaporte = $request->pasaporte;
        $persona->statusCTCT    = '';
        $persona->idRegionLT    = 0;
        $persona->anoEstudio    = '';
        $persona->idLocalidad   = $request->localidad;
        $persona->idProvincia   = $request->provincia;
        $persona->telefonoMovil = $request->telefono;
        $persona->idContactoCTCT    = '';
        $persona->apellidoPaterno   = $request->apellido;
        $persona->fechaNacimiento   = $fechaNacimiento;
        $persona->idPaisResidencia = $request->pais;
        $persona->idUnidadOrganizacional = 0;
    	$persona->save();
    	return $request;
    }

    public function validar_nuevo_mail(Request $request) {
    	return Persona::where('mail', $request->email)->get()->count();
    }
}
