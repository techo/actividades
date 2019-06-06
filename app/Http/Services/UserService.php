<?php

namespace App\Http\Services;

use App\Persona;
use App\VerificacionMailPersona;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Webpatser\Uuid\Uuid;

class UserService
{
    public function  crearUsuario(Request $request)
    {
        $persona = new Persona();
        $persona = $this->cargar_cambios($request, $persona);
        $persona->password = $this->setPassword(str_random(30));
        $persona->carrera = '';
        $persona->anoEstudio = '';
        $persona->idContactoCTCT = '';
        $persona->statusCTCT = '';
        $persona->lenguaje = '';
        $persona->idRegionLT = 0;
        $persona->idUnidadOrganizacional = 0;
        $persona->idCiudad = 0;
        $persona->verificado = false;
        $persona->recibirMails = 1;
        $persona->unsubscribe_token = Uuid::generate()->string;
        $persona->save();
        $verificacion = new VerificacionMailPersona();
        $verificacion->idPersona = $persona->idPersona;
        $verificacion->token = str_random(40);
        $verificacion->save();
        if (!empty($persona->idPersona) && $persona->assignRole($request->rol['rol'])) {
            return $persona;
        }

        return false;
    }

    public function cargar_cambios($request, $persona)
    {
        $fechaNacimiento = new Carbon($request->nacimiento);
        $persona->apellidoPaterno = $request->apellido;
        $persona->dni = $request->dni;
        $persona->mail = $request->email;
        $persona->fechaNacimiento = $fechaNacimiento;
        $persona->nombres = $request->nombre;
        $persona->idPais = $request->pais['id'];
        $persona->idPaisResidencia = $request->pais['id'];
        if($request->has('provincia') && $request->provincia != null)   {
            $persona->idProvincia = $request->provincia['id'];
        }
        if($request->has('provincia') && $request->provincia == null)   {
            $persona->idProvincia = null;
        }
        if($request->has('localidad') && $request->localidad != null) {
            $persona->idLocalidad = $request->localidad['id'];
        }
        if($request->has('localidad') && $request->localidad == null) {
            $persona->idLocalidad = null;
        }
        $persona->sexo = $request->sexo['id'];
        $persona->telefonoMovil = $request->telefono;
        if(!empty($request->password)) {
            $persona->password = Hash::make($request->password);
        }
        return $persona;
    }

    /**
     * @param $array
     * @return mixed
     */

    public function createValidator(Request $request)
    {
        $messages = [
            "sexo.required" => "El género es requerido",
            "nacimiento.required" => "El fecha de nacimiento es requerido",
            "dni.required" => "El DNI/Pasaporte es requerido",
            "pais.required" => "El país es requerido",
            "dni.regex" => "El DNI/Pasaporte tiene un formato inválido",
            "email.unique" => "El email ya existe en el sistema",
            "telefono.required" => "El teléfono es requerido",
            "telefono.regex" => "El teléfono debe ser numérico, simbolos permitidos: espacios + ( ) - o x .",
            "password.min" => "La contraseña debe tener un mínimo de 8 caracteres"
        ];
        $v = Validator::make(
            $request->all(),
            [
                'nombre' => 'required|regex:/^[\pL\s\.\']+$/ui',
                'apellido' => 'required|regex:/^[\pL\s\.\']+$/ui',
                'rol' => 'required',
                'pais' => 'required',
                'sexo' => 'required',
                'nacimiento' => 'required|date|before:' . date('Y-m-d'),
                'telefono' => ['required', 'regex:/^(\d|[\ \+\(\)\-\x\.])+$/ui'],
                'dni' => 'required|regex:/^[A-Za-z]{0,2}[0-9]{7,8}[A-Za-z]{0,2}$/',
                'email' => 'required|unique:Persona,mail,'.$request->id.',idPersona|email',
                'password' => 'sometimes|required|min:8|confirmed'
            ], $messages
        );

        return $v;
    }

    public function setPassword($string){
        return Hash::make($string);
    }

    public function  editarUsuario(Request $request)
    {
        $persona = Persona::findOrFail($request->idUsuario);

        $persona = $this->cargar_cambios($request, $persona);

        $persona->carrera = '';
        $persona->anoEstudio = '';
        $persona->idContactoCTCT = '';
        $persona->statusCTCT = '';
        $persona->lenguaje = '';
        $persona->idRegionLT = 0;
        $persona->idUnidadOrganizacional = 0;
        $persona->idCiudad = 0;

        $persona->save();

        if (!empty($persona->idPersona) && $persona->assignRole($request->rol['rol'])) {
            return $persona;
        }

        return false;
    }
}
