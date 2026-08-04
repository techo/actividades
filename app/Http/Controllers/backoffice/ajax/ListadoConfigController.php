<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\ListadoColumna;
use App\ListadoColumnaValor;
use App\ListadoPreferencia;
use App\ListadoVista;
use App\Persona;
use App\Services\Listados\Filtros\Operadores;
use App\Services\Listados\ListadoQuery;
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
        $cfg = $catalogo->config($contextId);

        $preferencia = ListadoPreferencia::where('persona_id', auth()->id())
            ->where('list_key', $listKey)
            ->where('context_id', $contextId)
            ->first();

        return response()->json(array_merge($cfg, [
            'columnas_custom' => $this->columnasDelContexto($listKey, $contextId)->values(),
            'preferencias' => $preferencia ? $preferencia->columnas : null,
            'filtrables' => $this->filtrables($catalogo, $contextId, $cfg),
            'agrupables' => $this->agrupables($catalogo, $contextId, $cfg),
        ]));
    }

    /**
     * Campos filtrables con label + operadores permitidos, para el constructor
     * de condiciones. El label se toma del título de la columna en el catálogo.
     */
    private function filtrables($catalogo, $contextId, array $cfg): array
    {
        $labels = $this->mapaLabels($cfg);
        $grupos = $this->mapaGrupos($cfg);

        $filtrables = [];
        foreach ($catalogo->filterableFields($contextId) as $key => $desc) {
            $grupo = $grupos[$key] ?? ['key' => 'otros', 'label' => 'backend.other'];
            $filtrables[] = [
                'key' => $key,
                'label' => $labels[$key] ?? $key,
                'type' => $desc['type'],
                'operadores' => Operadores::permitidos($desc['type']),
                'opciones' => $desc['opciones'] ?? null,
                // Grupo del catálogo (misma categorización que el panel de columnas).
                'grupo' => $grupo['key'],
                'grupo_label' => $grupo['label'],
            ];
        }

        return $filtrables;
    }

    private function agrupables($catalogo, $contextId, array $cfg): array
    {
        $labels = $this->mapaLabels($cfg);

        return array_map(function ($key) use ($labels) {
            return ['key' => $key, 'label' => $labels[$key] ?? $key];
        }, $catalogo->groupableFields($contextId));
    }

    /**
     * key => title de todas las columnas del catálogo (fijas + grupos), para
     * mostrar labels legibles en filtros/agrupación.
     */
    private function mapaLabels(array $cfg): array
    {
        $labels = [];
        foreach ($cfg['fijas'] ?? [] as $campo) {
            if (isset($campo['key'], $campo['title'])) {
                $labels[$campo['key']] = $campo['title'];
            }
        }
        foreach ($cfg['grupos'] ?? [] as $grupo) {
            foreach ($grupo['campos'] ?? [] as $campo) {
                if (isset($campo['key'], $campo['title'])) {
                    $labels[$campo['key']] = $campo['title'];
                }
            }
        }

        return $labels;
    }

    /**
     * key => {key, label} del grupo del catálogo al que pertenece cada columna,
     * para agrupar el selector de campos del filtro igual que el panel de columnas.
     */
    private function mapaGrupos(array $cfg): array
    {
        $grupos = [];
        foreach ($cfg['grupos'] ?? [] as $grupo) {
            foreach ($grupo['campos'] ?? [] as $campo) {
                if (isset($campo['key'])) {
                    $grupos[$campo['key']] = ['key' => $grupo['key'], 'label' => $grupo['titulo']];
                }
            }
        }

        return $grupos;
    }

    /**
     * Recuento de coincidencias del listado con los filtros actuales, y
     * opcionalmente el preview de una condición todavía no aplicada.
     *   ?filter=&condiciones[]=&preview={campo,condicion,valor}
     *   → { total: N, preview?: M }
     */
    public function count(Request $request, $listKey, $contextId)
    {
        $this->resolver($listKey, $contextId);

        $lq = new ListadoQuery();
        $filtros = $lq->filtrosDesdeRequest($request, $listKey, $contextId);

        $respuesta = ['total' => $lq->contar($listKey, $filtros)];

        if ($request->has('preview')) {
            $preview = $request->input('preview');
            $preview = is_string($preview) ? json_decode($preview, true) : $preview;
            if (is_array($preview) && isset($preview['campo'])) {
                $respuesta['preview'] = $lq->contarPreview($listKey, $filtros, $preview);
            }
        }

        return response()->json($respuesta);
    }

    /**
     * Recuento por grupo (facets) para el selector "Agrupar por".
     *   ?group_by=<fieldKey>&filter=&condiciones[]=
     *   → { field, buckets: [{valor,label,total}], sin_valor }
     */
    public function facets(Request $request, $listKey, $contextId)
    {
        $this->resolver($listKey, $contextId);

        $groupBy = $request->input('group_by');
        if (!$groupBy) {
            return response()->json(['field' => null, 'buckets' => [], 'sin_valor' => 0]);
        }

        $lq = new ListadoQuery();
        $filtros = $lq->filtrosDesdeRequest($request, $listKey, $contextId);

        return response()->json((new \App\Services\Listados\Facetador())->facetar($listKey, $filtros, $groupBy));
    }

    /**
     * Vistas del listado: predefinidas (código, read-only) + propias del usuario.
     *   → { predefinidas: [{id,nombre,color,config,es_predefinida}], propias: [...] }
     */
    public function vistas($listKey, $contextId)
    {
        $listado = $this->resolver($listKey, $contextId);
        $catalogo = app($listado['catalogo']);

        $predefinidas = [];
        foreach ($catalogo->defaultViews($contextId) as $i => $vista) {
            $predefinidas[] = [
                'id' => 'pred:' . $i,
                'nombre' => __($vista['nombre']),
                'color' => $vista['color'] ?? null,
                'config' => $vista['config'] ?? ['filtros' => [], 'group_by' => null],
                'es_predefinida' => true,
            ];
        }

        $propias = ListadoVista::where('persona_id', auth()->id())
            ->where('list_key', $listKey)
            ->where('context_id', $contextId)
            ->orderBy('orden')->orderBy('id')
            ->get()
            ->map(function ($vista) {
                return [
                    'id' => $vista->id,
                    'nombre' => $vista->nombre,
                    'color' => $vista->color,
                    'config' => $vista->config,
                    'es_predefinida' => false,
                ];
            });

        return response()->json(['predefinidas' => $predefinidas, 'propias' => $propias]);
    }

    public function guardarVista(Request $request, $listKey, $contextId)
    {
        $listado = $this->resolver($listKey, $contextId);

        $this->validate($request, [
            'nombre' => 'required|string|max:100',
            'color' => 'nullable|string|max:20',
            'config' => 'present|array',
        ]);

        $config = $this->validarConfigVista($listado, $contextId, $request->input('config', []));

        $vista = ListadoVista::create([
            'persona_id' => auth()->id(),
            'list_key' => $listKey,
            'context_id' => $contextId,
            'nombre' => $request->nombre,
            'color' => $request->color,
            'config' => $config,
            'orden' => ListadoVista::where('persona_id', auth()->id())
                    ->where('list_key', $listKey)->where('context_id', $contextId)->max('orden') + 1,
        ]);

        return response()->json(['vista' => $vista]);
    }

    public function actualizarVista(Request $request, $listKey, $contextId, $vistaId)
    {
        $listado = $this->resolver($listKey, $contextId);
        $vista = $this->vistaPropia($listKey, $contextId, $vistaId);

        $this->validate($request, [
            'nombre' => 'sometimes|required|string|max:100',
            'color' => 'sometimes|nullable|string|max:20',
            'config' => 'sometimes|array',
            'orden' => 'sometimes|integer',
        ]);

        $datos = $request->only(['nombre', 'color', 'orden']);
        if ($request->has('config')) {
            $datos['config'] = $this->validarConfigVista($listado, $contextId, $request->input('config', []));
        }
        $vista->update($datos);

        return response()->json(['vista' => $vista]);
    }

    public function eliminarVista($listKey, $contextId, $vistaId)
    {
        $this->resolver($listKey, $contextId);
        $this->vistaPropia($listKey, $contextId, $vistaId)->delete();

        return response()->json(['ok' => true]);
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

    private function vistaPropia($listKey, $contextId, $vistaId): ListadoVista
    {
        return ListadoVista::where('persona_id', auth()->id())
            ->where('list_key', $listKey)
            ->where('context_id', $contextId)
            ->findOrFail($vistaId);
    }

    /**
     * Valida que el config de una vista solo referencie campos consultables:
     * cada filtro debe apuntar a un campo filtrable y group_by a uno agrupable.
     * Rechaza agregados post-paginación (participaciones, evaluacion_general).
     */
    private function validarConfigVista(array $listado, $contextId, array $config): array
    {
        $catalogo = app($listado['catalogo']);
        $filtrables = array_keys($catalogo->filterableFields($contextId));
        $agrupables = $catalogo->groupableFields($contextId);

        $filtros = [];
        foreach ($config['filtros'] ?? [] as $filtro) {
            if (!isset($filtro['campo']) || !in_array($filtro['campo'], $filtrables, true)) {
                throw ValidationException::withMessages([
                    'config' => ["El campo '" . ($filtro['campo'] ?? '') . "' no es filtrable."],
                ]);
            }
            $filtros[] = [
                'campo' => $filtro['campo'],
                'condicion' => $filtro['condicion'] ?? '=',
                'valor' => $filtro['valor'] ?? null,
            ];
        }

        $groupBy = $config['group_by'] ?? null;
        if ($groupBy !== null && !in_array($groupBy, $agrupables, true)) {
            throw ValidationException::withMessages([
                'config' => ["El campo '$groupBy' no es agrupable."],
            ]);
        }

        return [
            'filtros' => $filtros,
            'group_by' => $groupBy,
            'columnas' => array_values(array_filter((array) ($config['columnas'] ?? []), 'is_string')),
            'sort' => isset($config['sort']) ? (string) $config['sort'] : null,
        ];
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
