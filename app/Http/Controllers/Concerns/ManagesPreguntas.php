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

    // ── Internos ────────────────────────────────────────────────────────────

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
        $pregunta->orden       = $request->input('orden', isset($pregunta->orden) ? $pregunta->orden : 0);
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
