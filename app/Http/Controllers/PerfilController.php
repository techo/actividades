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

	public function cambiar_email()
    {
    	$persona = Auth::user();
		$usuario = new PerfilResource($persona);
		return view('perfil.cambiar_email', compact('usuario'));
    }

    public function actualizar_email(Request $request)
    {
    	$persona = Auth::user();

    	$request->validate(['email' => 'required|unique:Persona,mail,'. $persona->idPersona .',idPersona|email']);

    	if($persona->mail == $request->email) {
    		$usuario = new PerfilResource($persona);
    		return view('perfil.cambiar_email', compact('usuario'));
    	}
		
		$persona->mail = $request->email;
		$persona->email_verified_at = null;
		$persona->save();

		$persona->notify(new \App\Notifications\VerifyEmail);

		Auth::logout();
		return redirect('/');
    }
}
