<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class PerfilController extends Controller
{
	public function show(Request $request) {
		$persona = Auth::user();
		$usuario = $persona->perfil();
		return view('perfil.index', compact('usuario'));
	}


	public function actividades(Request $request) {
		return view('perfil.actividades');
	}
}
