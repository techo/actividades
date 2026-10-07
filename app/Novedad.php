<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Mensaje de la barra celeste del backoffice ("Qué mejoramos", avisos). La barra rota
 * entre las activas; se cargan con `php artisan novedades` (no hay ABM a propósito).
 */
class Novedad extends Model
{
    protected $table = "novedades";

    protected $fillable = ['texto', 'link', 'activa'];

    protected $casts = ['activa' => 'boolean'];

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }
}
