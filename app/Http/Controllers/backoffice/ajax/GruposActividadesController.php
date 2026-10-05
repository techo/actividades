<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\Exports\ActividadesExport;
use App\Grupo;
use App\GrupoRolPersona;
use App\Http\Controllers\BaseController;
use App\Inscripcion;
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
    public function update(Actividad $id, Request $request)
    {
        $grupoArray = [];
        $personaArray = [];
        try {
            foreach ($request->miembros as $item) {
                if ($item['tipo'] === 'grupo' && $item['id'] !== $request->idGrupoDestino) {
                    $grupoArray[] = $item['id'];
                }

                if ($item['tipo'] === 'persona') {
                    $personaArray[] = $item['id'];
                }
            }

            if (count($grupoArray) > 0) {
                $grupos = Grupo::whereIn('idGrupo', $grupoArray)
                    ->where('idActividad', '=', $id->idActividad)
                    ->update(['idPadre' => (int)$request->idGrupoDestino]);
            }

            if (count($personaArray) > 0) {
                $personas = GrupoRolPersona::whereIn('idPersona', $personaArray)
                    ->where('idActividad', '=', $id->idActividad)
                    ->update(['idGrupo' => (int)$request->idGrupoDestino]);
            }

        } catch (\Exception $exception) {
            return response('Error al mover miembros: ' . $exception->getMessage(), 500);
        }

        return response('ok');
    }
    public function updateRol(Actividad $id, Request $request)
    {
        $personaArray = [];
        try {
            foreach ($request->miembros as $item) {
                if ($item['tipo'] === 'persona') {
                    $personaArray[] = $item['id'];
                }
            }

            if (count($personaArray) > 0) {
                $personas = Inscripcion::whereIn('idPersona', $personaArray)
                    ->where('idActividad', '=', $id->idActividad)
                    ->update(['rol' => $request->rol]);
            }

        } catch (\Exception $exception) {
            return response('Error al Cambiar Rol: ' . $exception->getMessage(), 500);
        }

        return response('ok');
    }

    public function updateLink(Actividad $id, Request $request)
    {
        // Definir las reglas de validación en una variable
        $rules = [
            'linkSeleccionado' => 'required|url',
            'miembros' => 'required|array',
            'miembros.*.tipo' => 'required|string|in:grupo,persona',
            'miembros.*.id' => 'required|integer',
        ];

        // Validar los datos entrantes
        $validatedData = $request->validate($rules);

        $grupoArray = [];

        try {
            foreach ($validatedData['miembros'] as $item) {
                if ($item['tipo'] === 'grupo') {
                    $grupoArray[] = $item['id'];
                }
            }

            if (count($grupoArray) > 0) {
                if (!empty($validatedData['linkSeleccionado'])) {
                    $validatedData['linkSeleccionado'] = rtrim(strstr($validatedData['linkSeleccionado'], '/viewform', true), '/') . '/';
                }
                Grupo::whereIn('idGrupo', $grupoArray)
                    ->where('idActividad', '=', $id->idActividad)
                    ->update(['linkEvaluacion' => $validatedData['linkSeleccionado']]);
            }

        } catch (\Exception $exception) {
            return response('Error al actualizar los links: ' . $exception->getMessage(), 500);
        }

        return response('ok');
    }

    public function delete(Actividad $id, Request $request)
    {
        $idsGrupo = [];
        $idsPersona = [];
        foreach ($request->miembros as $miembro) {
            if ($miembro['tipo'] === 'grupo') {
                $idsGrupo[] = $miembro['id'];
            }

            if ($miembro['tipo'] === 'persona') {
                $idsPersona[] = $miembro['id'];
            }
        }

        $grupoRaiz = $id->obtenerGrupoRaiz();

        if (count($idsGrupo) > 0) {
            // Solo grupos de ESTA actividad (y nunca la raíz), con todos sus descendientes.
            $grupos = Grupo::whereIn('idGrupo', $idsGrupo)
                ->where('idActividad', $id->idActividad)
                ->where('idGrupo', '<>', $grupoRaiz->idGrupo)
                ->get();
            $idsABorrar = $this->idsConDescendientes($grupos);

            if (count($idsABorrar) > 0) {
                GrupoRolPersona::whereIn('idGrupo', $idsABorrar)
                    ->where('idActividad', $id->idActividad)
                    ->update(['idGrupo' => $grupoRaiz->idGrupo]);
                Grupo::whereIn('idGrupo', $idsABorrar)
                    ->where('idActividad', $id->idActividad)
                    ->delete();
            }
        }

        if (count($idsPersona) > 0) {
            // "Borrar" una persona del árbol = devolverla a la raíz DE ESTA actividad (como dice el
            // modal: "van a ser re-asignadas al grupo raíz"). Antes se borraba su fila de
            // Grupo_Persona sin filtrar actividad → perdía la membresía en TODAS sus actividades y
            // después no se la podía volver a agregar ni aparecía para evaluar (reclamos #7/#11).
            GrupoRolPersona::whereIn('idPersona', $idsPersona)
                ->where('idActividad', $id->idActividad)
                ->update(['idGrupo' => $grupoRaiz->idGrupo]);
        }
        return response('ok');
    }

    /**
     * Ids de los grupos dados más todos sus descendientes (cualquier profundidad).
     */
    private function idsConDescendientes($grupos)
    {
        $ids = [];
        foreach ($grupos as $grupo) {
            $ids[] = $grupo->idGrupo;
            $ids = array_merge($ids, $this->idsConDescendientes($grupo->grupos));
        }
        return array_values(array_unique($ids));
    }
}
