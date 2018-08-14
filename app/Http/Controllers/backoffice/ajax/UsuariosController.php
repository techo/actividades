<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Resources\CoordinadorResource;
use App\Http\Resources\RolResource;
use App\Http\Resources\UsuariosResource;
use App\Persona;
use App\Search\UsuariosSearch;
use App\VerificacionMailPersona;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsuariosController extends Controller
{
    public function usuariosSearch(Request $request)
    {
        $filtros = $request->all();
        $result = UsuariosSearch::apply($filtros);
        $usuarios = CoordinadorResource::collection($result); //el nombre del resource no tiene sentido acá
        return response()->json($usuarios);
    }

    public function index(Request $request)
    {
        $filtro = $request->all();
        if($request->has('filter')){
            $filtro['usuario'] = $request->filter;
        }
        $result = UsuariosSearch::apply($filtro);
        $usuarios = UsuariosResource::collection($result); // Yo se que es horrible pero no funciona sin esto
        return response()->json($result);
    }

    public function getRol($id)
    {
        $rol = Persona::find($id)->roles()->first();
        return new RolResource($rol);
    }

    public function cargar_cambios($request,$persona)
    {
        $fechaNacimiento = new Carbon($request->nacimiento);
        $persona->apellidoPaterno = $request->apellido;
        $persona->dni = $request->dni;
        $persona->mail = $request->email;
        if(!empty($request->localidad)) {
            $persona->idLocalidad = $request->localidad['id'];
        }
        $persona->fechaNacimiento = $fechaNacimiento;
        $persona->nombres = $request->nombre;
        $persona->idPais = $request->pais['id'];
        $persona->idPaisResidencia = $request->pais['id'];
        if(!empty($request->provincia)) {
            $persona->idProvincia = $request->provincia['id'];
        }
        $persona->sexo = $request->sexo['id'];
        $persona->telefonoMovil = $request->telefono;
        return $persona;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    private function createValidator(Request $request)
    {
        $messages = [
            "sexo.required" => "El campo Género es requerido",
            "nacimiento.required" => "El campo Fecha de nacimiento es requerido",
            "dni.required" => "El campo DNI/Pasaporte es requerido",
            "pais.required" => "El campo País es requerido",
            "dni.regex" => "El campo DNI/Pasaporte tiene un formato inválido"
        ];
        $v = Validator::make(
            $request->all(),
            [
                'nombre' => 'required',
                'apellido' => 'required',
                'rol' => 'required',
                'pais' => 'required',
                'sexo' => 'required',
                'nacimiento' => 'required|date|before:' . date('Y-m-d'),
                'telefono' => 'required|numeric',
                'dni' => 'required|regex:/^[A-Za-z]{0,2}[0-9]{7,8}[A-Za-z]{0,2}$/',
            ], $messages
        );

        return $v;
    }

    public function store(Request $request) {
        $validator = $this->createValidator($request);

        if ($validator->passes()) {
            $persona = new Persona();
            $this->cargar_cambios($request, $persona);
            $persona->password = Hash::make(str_random(30));
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
            if (!empty($persona->idPersona) && $persona->assignRole($request->rol['rol'])) {
                return response()->json(['Usuario registrado correctamente'], 200);
            }

            return response()->json('Error desconocido', 500);
        }
        return response($validator->errors()->all(), 422);
    }
}
