<?php

namespace App\Services;

use App\Actividad;

/**
 * Computes the dynamic step sequence for an activity's enrollment flow.
 *
 * Steps are grouped by phase:
 *   'vue'   → handled inside inscripcion.vue (client-side multi-step)
 *   'blade' → server-side Blade views (confirmar, pago, finalizar)
 *
 * Usage:
 *   $steps = InscripcionFlow::stepsWithState($actividad, 'pago');
 *   // → array with 'state' => 'done' | 'active' | 'pending' for each step
 */
class InscripcionFlow
{
    /**
     * Master catalog — defines order, i18n key and phase for every possible step.
     * ORDER MATTERS: must match the real display order in inscripcion.vue.
     *
     * Vue flow order:
     *   1. ficha_medica     — shown first (fullscreen gate), before other steps
     *   2. rol
     *   3. tipo
     *   4. estudios
     *   5. jornadas
     *   6. preguntas
     *   7. punto_encuentro  — always last Vue step
     *
     * Conditions that determine inclusion live in steps().
     */
    private static $catalog = [
        ['key' => 'ficha_medica',    'label_key' => 'step_ficha_medica',    'phase' => 'vue'],
        ['key' => 'rol',             'label_key' => 'step_rol',             'phase' => 'vue'],
        ['key' => 'tipo',            'label_key' => 'step_tipo',            'phase' => 'vue'],
        ['key' => 'estudios',        'label_key' => 'step_estudios',        'phase' => 'vue'],
        ['key' => 'jornadas',        'label_key' => 'step_jornadas',        'phase' => 'vue'],
        ['key' => 'preguntas',       'label_key' => 'step_preguntas',       'phase' => 'vue'],
        ['key' => 'punto_encuentro', 'label_key' => 'step_punto_encuentro', 'phase' => 'vue'],
        ['key' => 'confirmar',       'label_key' => 'step_confirmar',       'phase' => 'blade'],
        ['key' => 'pago',            'label_key' => 'step_pago',            'phase' => 'blade'],
        ['key' => 'finalizar',       'label_key' => 'step_finalizar',       'phase' => 'blade'],
    ];

    /**
     * Return the ordered list of steps that are active for this activity.
     *
     * @param  Actividad $actividad
     * @return array<array{key:string, label_key:string, phase:string}>
     */
    public static function steps(Actividad $actividad): array
    {
        $steps = [];

        // ── Vue-managed sub-steps (same order the Vue component shows them) ─

        // Ficha médica is a fullscreen gate shown BEFORE all other steps
        if ($actividad->requiere_ficha_medica) {
            $steps[] = self::entry('ficha_medica');
        }

        if (!empty($actividad->roles_tags)) {
            $steps[] = self::entry('rol');
        }

        if (!empty($actividad->tipo_inscriptos_tag)) {
            $steps[] = self::entry('tipo');
        }

        if ($actividad->requiere_estudios) {
            $steps[] = self::entry('estudios');
        }

        // Jornadas: only if at least one active jornada exists
        if ($actividad->jornadas()->where('activo', 1)->exists()) {
            $steps[] = self::entry('jornadas');
        }

        if ($actividad->preguntas()->exists()) {
            $steps[] = self::entry('preguntas');
        }

        // Meeting-point selection is always the last Vue step
        $steps[] = self::entry('punto_encuentro');

        // ── Blade-managed steps ───────────────────────────────────────────
        $steps[] = self::entry('confirmar');

        if ($actividad->pago == 1) {
            $steps[] = self::entry('pago');
        }

        $steps[] = self::entry('finalizar');

        return $steps;
    }

    /**
     * Same as steps() but annotates each step with 'state' => 'done' | 'active' | 'pending'.
     *
     * Pass $phase to restrict the returned list to a single phase:
     *   'vue'   → only the Vue-managed steps (shown inside inscripcion.vue)
     *   'blade' → only the Blade-managed steps (confirmar / pago / finalizar)
     *   null    → all steps (default)
     *
     * @param  Actividad    $actividad
     * @param  string       $currentKey  Key of the step currently being shown
     * @param  string|null  $phase       Optional phase filter: 'vue' | 'blade' | null
     * @return array
     */
    public static function stepsWithState(Actividad $actividad, string $currentKey, string $phase = null): array
    {
        $steps = self::steps($actividad);

        // Filter by phase when requested (e.g. Blade views only want 'blade' steps)
        if ($phase !== null) {
            $steps = array_values(array_filter($steps, function (array $s) use ($phase) {
                return $s['phase'] === $phase;
            }));
        }

        $found = false;

        $result = array_map(function (array $step) use ($currentKey, &$found) {
            if ($step['key'] === $currentKey) {
                $step['state'] = 'active';
                $found = true;
            } elseif (!$found) {
                $step['state'] = 'done';
            } else {
                $step['state'] = 'pending';
            }

            return $step;
        }, $steps);

        // If the requested key is not present in the active steps for this activity
        // (e.g. passing 'pago' for an activity where pago==0), fall back to marking
        // the last step as active so the breadcrumb always has exactly one active item.
        if (!$found && !empty($result)) {
            $result[count($result) - 1]['state'] = 'active';
        }

        return $result;
    }

    // ── Private helpers ───────────────────────────────────────────────────

    private static function entry(string $key): array
    {
        foreach (self::$catalog as $entry) {
            if ($entry['key'] === $key) {
                return $entry;
            }
        }

        throw new \InvalidArgumentException("InscripcionFlow: unknown step key '{$key}'.");
    }
}
