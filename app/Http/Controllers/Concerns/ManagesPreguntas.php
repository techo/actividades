<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * Lógica compartida del CRUD de preguntas configurables (backoffice).
 *
 * Antes vivía duplicada en ActividadPreguntasController y CampaignPreguntasController.
 * Cada controlador concreto solo declara su modelo, su columna dueña y su alias
 * polimórfico; el comportamiento (validación, persistencia, condición) es único.
 *
 * Los métodos públicos index/store/update/destroy se mantienen en los
 * controladores concretos para no tocar el route-model binding ni las rutas.
 */
trait ManagesPreguntas
{
    /** Clase del modelo de pregunta (ActividadPregunta::class | CampaignPregunta::class). */
    abstract protected function preguntaClass();

    /** Columna que vincula la pregunta a su dueño ('actividad_id' | 'campaign_id'). */
    abstract protected function ownerColumn();

    protected function respondPreguntas($ownerId)
    {
        $class = $this->preguntaClass();

        $preguntas = $class::where($this->ownerColumn(), $ownerId)
            ->with('condiciones')
            ->orderBy('orden')
            ->get();

        return response()->json($preguntas);
    }

    protected function crearPregunta(Request $request, $ownerId)
    {
        $this->validarPregunta($request, $ownerId);

        $class = $this->preguntaClass();

        $pregunta = new $class();
        $pregunta->{$this->ownerColumn()} = $ownerId;
        $this->llenarPregunta($pregunta, $request);
        // El orden se autoasigna al final (máximo + 1) para que cada pregunta
        // nueva quede después de las existentes. Así "anterior = orden menor"
        // tiene sentido sin que el usuario tenga que numerar a mano.
        $pregunta->orden = $this->siguienteOrden($class, $ownerId);
        $pregunta->save();

        $this->sincronizarCondicion($pregunta, $request->input('condicion'));

        return response()->json($pregunta->load('condiciones'), 201);
    }

    protected function actualizarPregunta(Request $request, $ownerId, $preguntaId)
    {
        $class = $this->preguntaClass();

        $pregunta = $class::where($this->ownerColumn(), $ownerId)->findOrFail($preguntaId);

        $this->validarPregunta($request, $ownerId, $pregunta->id);

        $this->llenarPregunta($pregunta, $request);
        $pregunta->save();

        $this->sincronizarCondicion($pregunta, $request->input('condicion'));

        return response()->json($pregunta->load('condiciones'));
    }

    protected function eliminarPregunta($ownerId, $preguntaId)
    {
        $class = $this->preguntaClass();

        $pregunta = $class::where($this->ownerColumn(), $ownerId)->findOrFail($preguntaId);

        // Las condiciones donde esta pregunta es el TARGET se borran vía relación.
        $pregunta->condiciones()->delete();

        // Limpiar condiciones donde esta pregunta era el PADRE (quedarían colgadas).
        \App\PreguntaCondicion::where('parent_id', $pregunta->id)
            ->where('target_type', $pregunta->getMorphClass())
            ->delete();

        $pregunta->delete();

        return response()->json(null, 204);
    }

    /**
     * Mueve una pregunta una posición arriba/abajo intercambiándola con su vecina.
     *
     * Reindexa el orden a una secuencia contigua (0..n) — lo que de paso arregla
     * datos viejos que quedaron todos en orden 0 — y rechaza el movimiento si
     * dejaría una pregunta condicional ANTES de la pregunta de la que depende.
     */
    protected function moverPregunta(Request $request, $ownerId, $preguntaId)
    {
        $request->validate([
            'direccion' => 'required|in:arriba,abajo',
        ]);

        $class = $this->preguntaClass();

        $preguntas = $class::where($this->ownerColumn(), $ownerId)
            ->orderBy('orden')
            ->orderBy('id')
            ->get()
            ->values();

        $idx = $preguntas->search(function ($p) use ($preguntaId) {
            return (int) $p->id === (int) $preguntaId;
        });

        if ($idx === false) {
            abort(404);
        }

        $vecino = $request->input('direccion') === 'arriba' ? $idx - 1 : $idx + 1;

        // Si hay vecino válido, intercambiamos. En un extremo es un no-op
        // (igual reindexamos para normalizar el orden).
        if ($vecino >= 0 && $vecino < $preguntas->count()) {
            $tmp = $preguntas[$idx];
            $preguntas[$idx] = $preguntas[$vecino];
            $preguntas[$vecino] = $tmp;
        }

        // Orden propuesto = posición en el array.
        $nuevoOrden = [];
        foreach ($preguntas as $pos => $p) {
            $nuevoOrden[(int) $p->id] = $pos;
        }

        if (!$this->ordenRespetaCondiciones($class, array_keys($nuevoOrden), $nuevoOrden)) {
            return response()->json([
                'message' => 'No se puede mover: una pregunta condicional quedaría antes de la pregunta de la que depende.',
            ], 422);
        }

        foreach ($preguntas as $pos => $p) {
            if ((int) $p->orden !== $pos) {
                $p->orden = $pos;
                $p->save();
            }
        }

        return $this->respondPreguntas($ownerId);
    }

    // ── Internos ────────────────────────────────────────────────────────────

    /** Próximo valor de orden para el dueño (máximo actual + 1; 0 si no hay). */
    private function siguienteOrden($class, $ownerId)
    {
        $max = $class::where($this->ownerColumn(), $ownerId)->max('orden');

        return is_null($max) ? 0 : ((int) $max + 1);
    }

    /**
     * Verifica que, con el orden propuesto, toda condición tenga a su pregunta
     * padre con orden menor que el de la pregunta condicional (hija).
     */
    private function ordenRespetaCondiciones($class, array $ids, array $nuevoOrden)
    {
        if (empty($ids)) {
            return true;
        }

        $morph = (new $class())->getMorphClass();

        $condiciones = \App\PreguntaCondicion::where('target_type', $morph)
            ->whereIn('target_id', $ids)
            ->get();

        foreach ($condiciones as $cond) {
            $hijo  = (int) $cond->target_id;
            $padre = (int) $cond->parent_id;

            if (!isset($nuevoOrden[$hijo], $nuevoOrden[$padre])) {
                continue;
            }

            if ($nuevoOrden[$padre] >= $nuevoOrden[$hijo]) {
                return false;
            }
        }

        return true;
    }

    private function validarPregunta(Request $request, $ownerId, $preguntaId = null)
    {
        $request->validate([
            'pregunta'            => 'required|string|max:500',
            'descripcion'         => 'nullable|string|max:1000',
            'tipo'                => 'required|in:abierta,desplegable',
            'opciones'            => 'nullable|array',
            'requerida'           => 'boolean',
            'orden'               => 'integer',
            'condicion'           => 'nullable|array',
            'condicion.parent_id' => 'nullable|integer',
            'condicion.operator'  => 'nullable|string|in:equals',
            'condicion.value'     => 'nullable|string',
        ]);
    }

    private function llenarPregunta($pregunta, Request $request)
    {
        $pregunta->pregunta    = $request->pregunta;
        $pregunta->descripcion = $request->descripcion;
        $pregunta->tipo        = $request->tipo;
        // El mutator del trait Preguntable normaliza a [{id,label}] y conserva ids.
        $pregunta->opciones    = $request->tipo === 'desplegable' ? $request->opciones : null;
        $pregunta->requerida   = $request->input('requerida', false);
        // `orden` NO se toma del request: se autoasigna al crear y se preserva al
        // actualizar. El reordenamiento se hace por el endpoint `mover`.
    }

    /**
     * Sincroniza la condición de visibilidad (v1: una sola, operador 'equals').
     * Reemplaza la condición existente: borra y recrea si corresponde.
     */
    private function sincronizarCondicion($pregunta, $condicion)
    {
        $pregunta->condiciones()->delete();

        if (!is_array($condicion)) {
            return;
        }

        $parentId = isset($condicion['parent_id']) ? (int) $condicion['parent_id'] : null;
        $value    = isset($condicion['value']) ? $condicion['value'] : null;

        // Sin padre o sin valor → no hay condición válida (queda sin condición).
        if (!$parentId || $value === null || $value === '') {
            return;
        }

        // Anti auto-referencia.
        if ($parentId === (int) $pregunta->id) {
            return;
        }

        // El padre debe ser una pregunta del mismo dueño.
        $class = $this->preguntaClass();
        $ownerId = $pregunta->{$this->ownerColumn()};
        $padreValido = $class::where($this->ownerColumn(), $ownerId)
            ->where('id', $parentId)
            ->exists();

        if (!$padreValido) {
            return;
        }

        $pregunta->condiciones()->create([
            'parent_id' => $parentId,
            'operator'  => isset($condicion['operator']) ? $condicion['operator'] : 'equals',
            'value'     => $value,
        ]);
    }
}
