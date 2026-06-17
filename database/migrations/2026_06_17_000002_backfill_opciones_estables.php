<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Migra las opciones existentes de las preguntas desplegables del shape legacy
 * (array de strings: ["Zona Norte", ...]) al shape con identidad estable
 * ([{ "id": "opt_xxxx", "label": "Zona Norte" }, ...]).
 *
 * Idempotente: si una fila ya tiene el shape nuevo, se la deja intacta.
 * No toca la columna `respuesta` de las respuestas (se siguen guardando como texto).
 */
class BackfillOpcionesEstables extends Migration
{
    private $tablas = ['actividad_preguntas', 'campaign_preguntas'];

    public function up()
    {
        foreach ($this->tablas as $tabla) {
            DB::table($tabla)
                ->whereNotNull('opciones')
                ->orderBy('id')
                ->each(function ($fila) use ($tabla) {
                    $opciones = json_decode($fila->opciones, true);

                    if (!is_array($opciones) || empty($opciones)) {
                        return;
                    }

                    // Ya migrada: el primer elemento es objeto con id.
                    if (is_array($opciones[0]) && isset($opciones[0]['id'])) {
                        return;
                    }

                    $normalizadas = array_map(function ($opcion) {
                        if (is_array($opcion)) {
                            $label = isset($opcion['label']) ? $opcion['label']
                                   : (isset($opcion['text']) ? $opcion['text'] : '');
                            $id = isset($opcion['id']) && $opcion['id'] !== ''
                                ? $opcion['id'] : 'opt_' . Str::random(8);
                        } else {
                            $label = $opcion;
                            $id = 'opt_' . Str::random(8);
                        }

                        return ['id' => $id, 'label' => (string) $label];
                    }, $opciones);

                    DB::table($tabla)
                        ->where('id', $fila->id)
                        ->update(['opciones' => json_encode($normalizadas)]);
                });
        }
    }

    /**
     * Revierte al shape legacy (array de strings) para no dejar datos en un
     * formato que un eventual rollback no entienda.
     */
    public function down()
    {
        foreach ($this->tablas as $tabla) {
            DB::table($tabla)
                ->whereNotNull('opciones')
                ->orderBy('id')
                ->each(function ($fila) use ($tabla) {
                    $opciones = json_decode($fila->opciones, true);

                    if (!is_array($opciones) || empty($opciones)) {
                        return;
                    }

                    if (!is_array($opciones[0])) {
                        return; // ya está en shape legacy
                    }

                    $labels = array_map(function ($opcion) {
                        return isset($opcion['label']) ? $opcion['label'] : '';
                    }, $opciones);

                    DB::table($tabla)
                        ->where('id', $fila->id)
                        ->update(['opciones' => json_encode($labels)]);
                });
        }
    }
}
