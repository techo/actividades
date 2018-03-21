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
    	$persona->apellidoPaterno = $request->apellido;
    	$persona->dni = $request->dni;
    	$persona->mail = $request->email;
    	$persona->idLocalidad = $request->localidad;
    	$persona->fechaNacimiento = $fechaNacimiento;
    	$persona->nombres = $request->nombre;
    	$persona->idPais = $request->pais;
    	$persona->idPaisResidencia = $request->pais;
    	$persona->pasaporte = $request->pasaporte;
    	$persona->password = Hash::make($request->pass);
    	$persona->idProvincia = $request->provincia;
    	$persona->sexo = $request->sexo;
    	$persona->telefonoMovil = $request->telefono;
    	$persona->carrera = '';
    	$persona->anoEstudio = '';
    	$persona->idContactoCTCT = '';
    	$persona->statusCTCT = '';
    	$persona->lenguaje = '';
    	$persona->idRegionLT = 0;
    	$persona->idUnidadOrganizacional = 0;
    	$persona->idCiudad = 0;
    	$persona->save();
    	return $request;
    }

    public function validar_nuevo_mail(Request $request) {
    	return Persona::where('mail', $request->email)->get()->count();
    }
}
