<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class PerfilController extends Controller
{
	public function show() {
		$persona = Auth::user();
		$usuario = [
			'id' => $persona->idPersona,
			'email' => $persona->mail,
			'nombre' => $persona->nombres,
			'apellido' => $persona->apellidoPaterno,
			'nacimiento' => $persona->fechaNacimiento,
			'sexo' => $persona->sexo,
			'dni' => $persona->dni,
			'pais' => $persona->idPais,
			'provincia' => $persona->idProvincia,
			'localidad' => $persona->idLocalidad,
			'telefono' => $persona->telefonoMovil,
			'pass' => 'password'
		];
		return view('perfil', compact('usuario'));
	}    
}
