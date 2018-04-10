<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class PerfilController extends Controller
{
	public function show(Request $request) {
		$persona = Auth::user();
		$usuario = $persona->perfil();
		return view('perfil', compact('usuario'));
	}

}
