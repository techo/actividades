<?php

namespace App\Http\Controllers;

use App\Http\Resources\PerfilResource;
use Illuminate\Http\Request;
use Auth;

class PerfilController extends Controller
{
	public function show(Request $request) {
		$persona = Auth::user();
		$usuario = new PerfilResource($persona);
		return view('perfil.index', compact('usuario'));
	}


	public function actividades(Request $request) {
		return view('perfil.actividades');
	}
}
