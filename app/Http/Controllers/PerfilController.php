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
        $datatableConfig = config('datatables.voluntario-actividades');
        $fields = json_encode($datatableConfig['fields']);
	    $sortOrder = json_encode($datatableConfig['sortOrder']);
		return view('perfil.actividades', compact('fields', 'sortOrder'));
	}
}
