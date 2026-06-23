<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Montos sugeridos de donación por país (Amigos de TECHO).
 *
 * Los valores NO se calculan por tipo de cambio: están fijados por producto
 * y se editan directamente en BD (ver DonationPresetsSeeder). Una fila por país.
 *
 * Cuando un país no tiene fila, el endpoint cae al default global
 * (DEFAULT_CURRENCY / DEFAULT_PRESETS / DEFAULT_EXPONENT).
 */
class DonationPreset extends Model
{
    protected $table = 'donation_presets';

    protected $fillable = [
        'id_pais',
        'currency',
        'preset_low',
        'preset_mid',
        'preset_high',
        'minor_unit_exponent',
        'pix_enabled',
    ];

    protected $casts = [
        'id_pais'             => 'integer',
        'preset_low'          => 'integer',
        'preset_mid'          => 'integer',
        'preset_high'         => 'integer',
        'minor_unit_exponent' => 'integer',
        'pix_enabled'         => 'boolean',
    ];

    // ── Default global para países sin presets configurados ───────────────
    const DEFAULT_CURRENCY = 'usd';
    const DEFAULT_PRESETS  = ['bajo' => 5, 'medio' => 10, 'alto' => 20];
    const DEFAULT_EXPONENT = 2;

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'id_pais', 'id');
    }
}
