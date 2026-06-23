<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Condición de visibilidad de una pregunta.
 *
 * Una pregunta (target) se muestra solo si su pregunta padre (parent) tiene una
 * respuesta determinada. El valor esperado se guarda como el `id` estable de la
 * opción del padre (no su texto), de modo que renombrar la opción no rompe la
 * condición.
 *
 * Diseño preparado para evolución (sin migraciones traumáticas):
 *   - Hoy: 1 condición por pregunta, operador 'equals'.
 *   - Mañana: N filas por pregunta + columnas nullable (logic_operator, group)
 *     para AND/OR y grupos; o nuevos `operator`. La estructura ya lo admite.
 *
 * Polimórfica vía morphMap ('actividad_pregunta' | 'campaign_pregunta') para no
 * acoplar la BD a namespaces PHP.
 */
class PreguntaCondicion extends Model
{
    protected $table = 'pregunta_condiciones';
    protected $primaryKey = 'id';

    protected $fillable = [
        'target_type',
        'target_id',
        'parent_id',
        'operator',
        'value',
    ];

    protected $attributes = [
        'operator' => 'equals',
    ];

    /**
     * Pregunta que se muestra condicionalmente.
     */
    public function target()
    {
        return $this->morphTo();
    }
}
