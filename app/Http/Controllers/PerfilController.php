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
		$fichaMedica = \App\FichaMedica::where('idPersona', $persona->idPersona)->get();
		$usuario = new PerfilResource($persona);
		return view('perfil.index', compact('usuario', 'fichaMedica'));
	}


	public function actividades(Request $request) {
        $datatableConfig = config('datatables.voluntario-actividades');
        $fields = json_encode($datatableConfig['fields']);
	    $sortOrder = json_encode($datatableConfig['sortOrder']);
		return view('perfil.actividades', compact('fields', 'sortOrder'));
	}

	public function evaluacion(Request $request) {
		$persona = Auth::user();

        $promedioTecnico = \App\EvaluacionPersona::where('idEvaluado', '=', $persona->idPersona)
        	->where('puntajeTecnico','>',0)
        	->avg('puntajeTecnico');

        $promedioSocial = \App\EvaluacionPersona::where('idEvaluado', '=', $persona->idPersona)
        	->where('puntajeSocial','>',0)
        	->avg('puntajeSocial');

        $promedioGenero = \App\EvaluacionPersona::where('idEvaluado', '=', $persona->idPersona)
        	->where('puntajeGenero','>',0)
        	->avg('puntajeGenero');

        if(!$promedioTecnico) $promedioTecnico = 0;
        if(!$promedioSocial) $promedioSocial = 0;
        if(!$promedioGenero) $promedioGenero = 0;

		return view('perfil.evaluaciones', compact('promedioTecnico', 'promedioSocial', 'promedioGenero'));
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
