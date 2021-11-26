<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CrearPersona;
use App\Persona;
use Illuminate\Support\Facades\Auth;



class PersonasController extends Controller
{ 
    public function index()
    {
        return Persona::all();
    }

    public function show(Persona $persona)
    {
        return $persona;
    }

    public function getPersonaxMail($mail)
    {
        $persona = Persona::where('mail', $mail)->get();
        return $persona;
    }

    public function login(request $request)
    {
        $credentials = $request->only('mail', 'password');
        $authSuccess = Auth::attempt($credentials, $request->has('remember'));
        $afterLoginUrl = '';

        if ($authSuccess){ 
            return response(
                [
                    'success' => true,
                    'user' => Auth::user(),
                    'mensaje' => "Autenticación OK",
                ],
                200
            );
        } else {
            return response(
                [
                    'success' => false,
                    'user' => Auth::user(),
                    'mensaje' => "Error de autenticación",
                ],
                401
            );
        }
    }

    public function register(CrearPersona $request)
    {   
        $fields = $request->validated();

        $persona = Persona::create([
            'dni' => $fields['dni'],
            'nombres' => $fields['nombres'],
            'apellidoPaterno' => $fields['apellidoPaterno'],
            'mail' => $fields['mail'],
            'password' => bcrypt($fields['password']),
            'fechaNacimiento' => $fields['fechaNacimiento'],
            'telefono' => $fields['telefono'],
            'telefonoMovil' => $fields['telefonoMovil'],
            'recibirMails' => $fields['recibirMails'],
            'acepta_marketing' => $fields['acepta_marketing'],
            'idPais' => $fields['idPais'],
            'idUnidadOrganizacional' => $fields['idUnidadOrganizacional'],
        ]);
        
        return response(
                [
                    'success' => true,
                    'persona' => $persona,
                    'mensaje' => "Persona Creada, validacion de mail pendiente",
                ],
                201
            );
    }

    public function update(Request $request, Persona $persona)
    {
        $persona->update($request->all());

        return response()->json($persona, 200);
    }

    public function delete(Persona $persona)
    {
        $persona->delete();

        return response()->json(null, 204);
    }
}
