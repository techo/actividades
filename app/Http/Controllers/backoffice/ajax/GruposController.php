<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\BaseController;
use App\Http\Resources\MiembroResource;
use Illuminate\Http\Request;
use App\Grupo;

class GruposController extends BaseController
{
    public function index($idGrupo, Request $request)
    {
        $grupo = Grupo::findOrFail($idGrupo);
        $collection = [];
        $miembros = $grupo->miembros;
        foreach ($miembros as $i => $item) {
            $collection[] = new MiembroResource($item);
        }
        return $this->paginate($collection, 3);
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'nombre'        => 'required|max:254',
            'idPadre'       => 'required',
            'idActividad'   => 'required',
        ]);
        $grupo = Grupo::create($request->all());
        return $grupo;
    }
}
