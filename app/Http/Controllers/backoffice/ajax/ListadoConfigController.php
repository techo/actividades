<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\ListadoColumna;
use App\ListadoColumnaValor;
use App\ListadoPreferencia;
use App\Persona;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Endpoints genéricos de los listados con columnas configurables.
 * El list_key se resuelve contra el registry config/listados.php; la
 * autorización se aplica contra el context_id según la regla del registry.
 */
class ListadoConfigController extends Controller
{
    public function config($listKey, $contextId)
    {
        $listado = $this->resolver($listKey, $contextId);

        $catalogo = app($listado['catalogo']);

        $preferencia = ListadoPreferencia::where('persona_id', auth()->id())
            ->where('list_key', $listKey)
            ->where('context_id', $contextId)
            ->first();

        return response()->json(array_merge($catalogo->config($contextId), [
            'columnas_custom' => $this->columnasDelContexto($listKey, $contextId)->values(),
            'preferencias' => $preferencia ? $preferencia->columnas : null,
        ]));
    }

    public function guardarPreferencias(Request $request, $listKey, $contextId)
    {
        $this->resolver($listKey, $contextId);

        $this->validate($request, [
            'columnas' => 'present|array',
            'columnas.*' => 'string|max:100',
        ]);

        ListadoPreferencia::updateOrCreate(
            [
                'persona_id' => auth()->id(),
                'list_key' => $listKey,
                'context_id' => $contextId,
            ],
            ['columnas' => $request->columnas]
        );

        return response()->json(['ok' => true]);
    }

    public function crearColumna(Request $request, $listKey, $contextId)
    {
        $this->resolver($listKey, $contextId);

        $this->validate($request, [
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:' . implode(',', ListadoColumna::TIPOS),
            'opciones' => 'required_if:tipo,estado,etiquetas|array',
            'opciones.*' => 'string|max:100',
        ]);

        $columna = ListadoColumna::create([
            'list_key' => $listKey,
            'context_id' => $contextId,
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'opciones' => in_array($request->tipo, ['estado', 'etiquetas'])
                ? array_values(array_filter($request->opciones, 'strlen'))
                : null,
            'orden' => ListadoColumna::where('list_key', $listKey)
                    ->where('context_id', $contextId)->max('orden') + 1,
            'created_by' => auth()->id(),
        ]);

        return response()->json(['columna' => $columna]);
    }

    public function actualizarColumna(Request $request, $listKey, $contextId, $columnaId)
    {
        $this->resolver($listKey, $contextId);
        $columna = $this->columnaDelContexto($listKey, $contextId, $columnaId);

        // Tipo y opciones son inmutables: los valores guardados dependen de ellos.
        $this->validate($request, [
            'nombre' => 'sometimes|required|string|max:100',
            'orden' => 'sometimes|integer',
        ]);

        $columna->update($request->only(['nombre', 'orden']));

        return response()->json(['columna' => $columna]);
    }

    public function eliminarColumna($listKey, $contextId, $columnaId)
    {
        $this->resolver($listKey, $contextId);
        $columna = $this->columnaDelContexto($listKey, $contextId, $columnaId);

        // Soft delete: los valores cargados quedan en la tabla por si se restaura.
        $columna->delete();

        return response()->json(['ok' => true]);
    }

    public function guardarValor(Request $request, $listKey, $contextId, $columnaId, $recordId)
    {
        $listado = $this->resolver($listKey, $contextId);
        $columna = $this->columnaDelContexto($listKey, $contextId, $columnaId);

        if (!$this->recordPerteneceAlContexto($listado, $recordId, $contextId)) {
            abort(404);
        }

        $valor = $this->normalizarValor($columna, $request->input('valor'));

        if ($valor === null) {
            ListadoColumnaValor::where('columna_id', $columna->id)
                ->where('record_id', $recordId)
                ->delete();
        } else {
            ListadoColumnaValor::updateOrCreate(
                ['columna_id' => $columna->id, 'record_id' => $recordId],
                ['valor' => $valor, 'updated_by' => auth()->id()]
            );
        }

        return response()->json(['valor' => $valor]);
    }

    /**
     * Resuelve el list_key contra el registry y autoriza contra el contexto.
     */
    private function resolver($listKey, $contextId): array
    {
        $listado = config("listados.$listKey");
        if (!$listado) {
            abort(404);
        }

        $regla = $listado['authorize'];
        if (isset($regla['policy'])) {
            $this->authorize($regla['policy'], [$regla['model'], $contextId]);
        } elseif (isset($regla['roles'])) {
            if (!auth()->user()->hasAnyRole(explode('|', $regla['roles']))) {
                abort(403);
            }
        } else {
            abort(403);
        }

        return $listado;
    }

    private function columnasDelContexto($listKey, $contextId)
    {
        return ListadoColumna::where('list_key', $listKey)
            ->where('context_id', $contextId)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();
    }

    private function columnaDelContexto($listKey, $contextId, $columnaId): ListadoColumna
    {
        return ListadoColumna::where('list_key', $listKey)
            ->where('context_id', $contextId)
            ->findOrFail($columnaId);
    }

    private function recordPerteneceAlContexto(array $listado, $recordId, $contextId): bool
    {
        $regla = $listado['record'];
        $query = $regla['model']::where($regla['key'], $recordId);

        if (!empty($regla['context'])) {
            $query->where($regla['context'], $contextId);
        }

        return $query->exists();
    }

    /**
     * Valida el valor según el tipo de la columna y lo lleva a su forma
     * canónica de guardado. Devuelve null si el valor significa "vaciar".
     */
    private function normalizarValor(ListadoColumna $columna, $valor)
    {
        if ($valor === null || $valor === '' || $valor === []) {
            return null;
        }

        switch ($columna->tipo) {
            case 'casilla':
                if (!in_array($valor, [0, 1, '0', '1', true, false], true)) {
                    $this->valorInvalido('casilla');
                }
                return $valor && $valor !== '0' ? '1' : null; // desmarcada = sin valor

            case 'estado':
                if (!in_array($valor, $columna->opciones ?: [], true)) {
                    $this->valorInvalido('estado');
                }
                return $valor;

            case 'etiquetas':
                $etiquetas = is_array($valor) ? $valor : json_decode($valor, true);
                if (!is_array($etiquetas) || array_diff($etiquetas, $columna->opciones ?: [])) {
                    $this->valorInvalido('etiquetas');
                }
                return count($etiquetas) ? json_encode(array_values($etiquetas)) : null;

            case 'texto':
                if (!is_string($valor) || mb_strlen($valor) > 1000) {
                    $this->valorInvalido('texto');
                }
                return $valor;

            case 'fecha':
                $fecha = \DateTime::createFromFormat('Y-m-d', $valor);
                if (!$fecha || $fecha->format('Y-m-d') !== $valor) {
                    $this->valorInvalido('fecha');
                }
                return $valor;

            case 'persona':
                if (!Persona::where('idPersona', $valor)->exists()) {
                    $this->valorInvalido('persona');
                }
                return (string) $valor;
        }

        $this->valorInvalido($columna->tipo);
    }

    private function valorInvalido($tipo)
    {
        throw ValidationException::withMessages([
            'valor' => ["Valor inválido para una columna de tipo $tipo."],
        ]);
    }
}
