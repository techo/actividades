<?php

namespace App\Http\Controllers;

use App\Actividad;
use App\Http\Resources\PerfilResource;
use App\Inscripcion;
use App\Integrante;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

	public function quiz_techero()
    {
		return view('perfil.quizTechero');
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

    public function get_constancia_voluntariado(Request $request){
        
		$persona = Auth::user();
		$inscripciones = Inscripcion::where('idPersona', $persona->idPersona)
			->with('actividad')
			->where('presente')->get();
		$integrantes = Integrante::where('idPersona', $persona->idPersona)
			->with('equipo')
			->get();

		// Log::info($persona);
		// Log::info($inscripciones);
		Log::info($integrantes);

		$pdf = app('dompdf.wrapper');
        $pdf->loadView('pdf.constanciaVoluntariado', compact('persona', 'inscripciones', 'integrantes'));
        
        return $pdf->download('Constancia_Voluntariado_TECHO.pdf');
	}

}
