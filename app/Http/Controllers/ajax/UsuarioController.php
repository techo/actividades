<?php

namespace App\Http\Controllers\ajax;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Mail;
use App\Mail\VerificarMail;
use App\Persona;
use App\VerificacionMailPersona;
class UsuarioController extends Controller
{
    public function create(Request $request) {
        $url = $request->session()->get('login_callback','');
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
    	$persona->password = Hash::make($request->pass);
    	$persona->idProvincia = $request->provincia;
    	$persona->sexo = $request->sexo;
    	$persona->telefonoMovil = $request->telefono;
    	$persona->google_id = $request->google_id;
    	$persona->facebook_id = $request->facebook_id;
    	$persona->carrera = '';
    	$persona->anoEstudio = '';
    	$persona->idContactoCTCT = '';
    	$persona->statusCTCT = '';
    	$persona->lenguaje = '';
    	$persona->idRegionLT = 0;
    	$persona->idUnidadOrganizacional = 0;
    	$persona->idCiudad = 0;
        $persona->verificado = false;
        $persona->save();
        $verificacion = new VerificacionMailPersona();
        $verificacion->idPersona = $persona->idPersona;
        $verificacion->token = str_random(40);
        $verificacion->save();
        Auth::login($persona, true);
        $request->session()->regenerate();
        Mail::to($persona->mail)->send(new VerificarMail($persona));
        return ['login_callback' =>  $url];
    }

    public function validar_nuevo_mail(Request $request) {
    	return Persona::where('mail', $request->email)->get()->count();
    }

    public function linkear(Request $request) {
        $url = $request->session()->get('login_callback','');
        $success = false;
        $persona = Persona::where('mail', $request->email)->first();
        if($persona) {
            $success = true;
            if($request->media == 'google') {
                $persona->google_id = $request->id;
            }
            if($request->media == 'facebook') {
                $persona->facebook_id = $request->id;
            }
        }
        if($success) {
            $persona->save();
            Auth::login($persona, true);
            $request->session()->regenerate();
        }
        return ['success' => $success, 'login_callback' => $url];
    } 

}
