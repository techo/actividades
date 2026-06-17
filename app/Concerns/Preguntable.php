<?php

namespace App\Concerns;

use App\PreguntaCondicion;
use Illuminate\Support\Str;

/**
 * Comportamiento compartido por los modelos de pregunta configurable
 * (ActividadPregunta, CampaignPregunta).
 *
 * Centraliza:
 *   - fillable / casts comunes
 *   - identidad estable de opciones: en la BD las opciones se guardan como
 *     [{ "id": "opt_xxxx", "label": "Texto visible" }]. El `id` es inmutable y
 *     es lo que referencian las condiciones, de modo que renombrar el `label`
 *     no rompe ninguna condición.
 *   - relación polimórfica `condiciones`.
 *
 * Compatibilidad hacia atrás:
 *   - El accessor `opciones` SIGUE devolviendo un array de strings (los labels),
 *     idéntico al contrato anterior. Clientes viejos (app mobile, frontend
 *     actual) no notan el cambio.
 *   - Se expone además `opciones_detalle` ([{id,label}]) para los consumidores
 *     nuevos (modal de configuración y evaluador de condiciones).
 */
trait Preguntable
{
    /**
     * Inicialización por instancia (Laravel invoca initialize{Trait}).
     * Evita repetir casts/appends/fillable en cada modelo.
     */
    public function initializePreguntable()
    {
        // mergeFillable no existe en Laravel 5.7; merge manual sobre la propiedad.
        $this->fillable = array_values(array_unique(array_merge($this->fillable, [
            'pregunta',
            'descripcion',
            'tipo',
            'opciones',
            'requerida',
            'orden',
        ])));

        // `opciones` NO se castea a array: lo maneja el accessor/mutator de abajo
        // para poder normalizar el shape y exponer dos vistas (labels / detalle).
        $this->casts['requerida'] = 'boolean';

        if (!in_array('opciones_detalle', $this->appends)) {
            $this->appends[] = 'opciones_detalle';
        }
    }

    /**
     * Condiciones que determinan si esta pregunta debe mostrarse.
     * Polimórfica: la misma tabla sirve a Actividades y Campañas.
     */
    public function condiciones()
    {
        return $this->morphMany(PreguntaCondicion::class, 'target');
    }

    // ── Opciones: identidad estable ────────────────────────────────────────

    /**
     * Devuelve las opciones normalizadas como [{id, label}].
     * Tolera datos legacy (array de strings) por si quedara alguno sin migrar.
     */
    public function getOpcionesDetalleAttribute()
    {
        return self::normalizarOpciones($this->rawOpciones());
    }

    /**
     * Compatibilidad hacia atrás: array de strings (labels).
     * Es lo que esperan el frontend actual y la app mobile.
     */
    public function getOpcionesAttribute()
    {
        return array_map(function ($o) {
            return $o['label'];
        }, self::normalizarOpciones($this->rawOpciones()));
    }

    /**
     * Acepta tanto un array de strings como un array de {id,label} y persiste
     * siempre [{id,label}], generando ids para las opciones que no lo traigan.
     * Preservar el id existente es lo que mantiene vivas las condiciones.
     */
    public function setOpcionesAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['opciones'] = null;
            return;
        }

        $this->attributes['opciones'] = json_encode(self::normalizarOpciones($value));
    }

    private function rawOpciones()
    {
        $raw = isset($this->attributes['opciones']) ? $this->attributes['opciones'] : null;

        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }

        return is_array($raw) ? $raw : [];
    }

    /**
     * Normaliza una lista heterogénea a [{id,label}] con ids estables.
     */
    public static function normalizarOpciones($opciones)
    {
        if (!is_array($opciones)) {
            return [];
        }

        $resultado = [];

        foreach ($opciones as $opcion) {
            if (is_array($opcion)) {
                $label = isset($opcion['label']) ? $opcion['label']
                       : (isset($opcion['text']) ? $opcion['text'] : null);
                $id = isset($opcion['id']) && $opcion['id'] !== '' ? $opcion['id'] : null;
            } else {
                $label = $opcion;
                $id = null;
            }

            if ($label === null || $label === '') {
                continue;
            }

            $resultado[] = [
                'id'    => $id ?: self::generarOpcionId(),
                'label' => (string) $label,
            ];
        }

        return $resultado;
    }

    private static function generarOpcionId()
    {
        return 'opt_' . Str::random(8);
    }
}
