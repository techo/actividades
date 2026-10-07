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

    protected $fillable = ['texto', 'traducciones', 'locales', 'link', 'activa'];

    protected $casts = [
        'activa' => 'boolean',
        'traducciones' => 'array',
        'locales' => 'array',
    ];

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Si la novedad se muestra en ese locale. `locales` acepta el locale exacto o el
     * idioma base ("es" cubre es_AR y es_CH).
     */
    public function visiblePara($locale)
    {
        if (empty($this->locales)) {
            return true;
        }

        return in_array($locale, $this->locales) || in_array(self::idiomaBase($locale), $this->locales);
    }

    /** Texto en el locale pedido: exacto → idioma base (es_AR → es) → texto base. */
    public function textoPara($locale)
    {
        $traducciones = $this->traducciones ?: [];

        return $traducciones[$locale]
            ?? $traducciones[self::idiomaBase($locale)]
            ?? $this->texto;
    }

    private static function idiomaBase($locale)
    {
        return explode('_', (string) $locale)[0];
    }
}
