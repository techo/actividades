<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tipo extends Model
{
    use SoftDeletes;
    protected $table = "Tipo";
    protected $primaryKey = "idTipo";
    public $timestamps = false;
    protected $fillable = ['nombre', 'idCategoria', 'imagen', 'tipo_indicador', 'activo', 'nombre_pt', 'nombre_en'];
    protected $appends = ['nombre_localizado'];

    /**
     * Devuelve el nombre del tipo en el idioma activo de la sesión.
     * Fallback a español (nombre) si el campo no está completo.
     */
    public function getNombreLocalizadoAttribute(): string
    {
        $locale = \App::getLocale();

        if ($locale === 'pt' && !empty($this->nombre_pt)) {
            return $this->nombre_pt;
        }

        if ($locale === 'en' && !empty($this->nombre_en)) {
            return $this->nombre_en;
        }

        return $this->nombre;
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'idActividad');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaActividad::class, 'idCategoria', 'id');
    }
}
