<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\BaseController;
use App\Http\Resources\MiembroResource;
use Illuminate\Http\Request;
use App\Grupo;
use Illuminate\Support\Facades\DB;

class GruposController extends BaseController
{
    public function index($idGrupo, Request $request)
    {
        $perPage = $request->per_page ?? 10;
        $grupo = Grupo::findOrFail($idGrupo);
        $collection = [];
        $miembros = $grupo->miembros;
        foreach ($miembros as $i => $item) {
            $collection[] = new MiembroResource($item);
        }
        return $this->paginate($collection, $perPage);
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'nombre'        => 'required|max:254',
            'idPadre'       => 'required',
            'idActividad'   => 'required',
        ]);
        $grupo = Grupo::create($request->all());
        return new MiembroResource($grupo);
    }

    public function incluirInscripto($idGrupo, Request $request)
    {
        $validate = $request->validate([
            'idPersona'     => 'required|numeric',
            'idGrupo'       => 'required|numeric',
            'idActividad'   => 'required|numeric',
        ]);
        $id = DB::table('Grupo_Persona')->insertGetId($request->all());
        $result = array_merge($request->all(), ['idPersona' => $id, 'tipo' => 'persona']);
        return json_encode($result);
    }
}
