<?php

namespace App\Http\Controllers;

use App\Http\Resources\PerfilResource;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

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

	public function evaluacion(Request $request) {
		$persona = Auth::user();
        $evaluacionPersonal = \App\EvaluacionPersona::where('idEvaluado', '=', $persona->idPersona)
        	->select(DB::raw('ROUND(avg(puntajeSocial),1) as puntajeSocial,  ROUND(avg(puntajeTecnico),1) as puntajeTecnico'))
        	->get();

		return view('perfil.evaluaciones', compact('evaluacionPersonal'));
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

    	$request->validate([
    		'email' => [
    			'required',
    			'email',
    			'confirmed',
    			'unique:Persona,mail,'. $persona->idPersona .',idPersona',
    			'not_in:'. $persona->mail,
    		]
    	]);
		
		$persona->mail = $request->email;
		$persona->email_verified_at = null;

		//desvincular redes sociales
		$persona->facebook_id = null;		
		$persona->google_id = null;		

		$persona->save();

		$persona->notify(new \App\Notifications\VerifyEmail);

		Redirect::setIntendedUrl("/");

		Auth::logout();
		$request->session()->flash('mensaje', __('frontend.email_succesfully_updated'));

		return redirect('/');
    }
}
