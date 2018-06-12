<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\Exports\ActividadesExport;
use App\Grupo;
use App\GrupoRolPersona;
use App\Http\Controllers\BaseController;
use App\Persona;
use Illuminate\Http\Request;

class GruposActividadesController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $export = new ActividadesExport($request->filter, $request->sort);
        $collection = $export->collection();
        $result = $this->paginate($collection, 10);
        return $result;
    }

    public function update($id, Request $request)
    {
        $grupoArray = [];
        $personaArray = [];
        try {
            foreach ($request->miembros as $item) {
                if ($item['tipo'] === 'grupo') {
                    $grupoArray[] = $item['id'];
                }

                if ($item['tipo'] === 'persona') {
                    $personaArray[] = $item['id'];
                }
            }

            if (count($grupoArray) > 0) {
                $grupos = Grupo::whereIn('idGrupo', $grupoArray)
                    ->where('idActividad', '=', (int)$id)
                    ->update(['idPadre' => (int)$request->idGrupoDestino]);
            }

            if (count($personaArray) > 0) {
                $personas = GrupoRolPersona::whereIn('idPersona', $personaArray)
                    ->where('idActividad', '=', (int)$id)
                    ->update(['idGrupo' => (int)$request->idGrupoDestino]);
            }

        } catch (\Exception $exception) {
            return response('Error al mover miembros: ' . $exception->getMessage(), 500);
        }

        return response('ok');
    }
    public function updateRol($id, Request $request)
    {
        $personaArray = [];
        try {
            foreach ($request->miembros as $item) {
                if ($item['tipo'] === 'persona') {
                    $personaArray[] = $item['id'];
                }
            }

            if (count($personaArray) > 0) {
                $personas = GrupoRolPersona::whereIn('idPersona', $personaArray)
                    ->where('idActividad', '=', (int)$id)
                    ->update(['rol' => (int)$request->rol]);
            }

        } catch (\Exception $exception) {
            return response('Error al Cambiar Rol: ' . $exception->getMessage(), 500);
        }

        return response('ok');
    }

    public function delete($id, Request $request)
    {
        $result = $this->buscarRecursivo($request->miembros);
        return $result;
    }

    private function buscarRecursivo($lista)
    {
        $personas = '';
        $grupos = '';
        foreach ($lista as $item) {
            if ($this->esPersona($item)) {
                $personas .= $item['id'];
            } else {
                $grupos .= $item['id'];
                $grupos .= $this->buscarRecursivo((Grupo::find($item['id']))->miembros);
            }
        }
        $cuenta['personas'] = $personas;
        $cuenta['grupos'] = $grupos;
        return $cuenta;
    }

    private function esPersona($item)
    {
        return (!empty($item['tipo']) && $item['tipo'] === 'persona') || ($item instanceof Persona);
    }

}
