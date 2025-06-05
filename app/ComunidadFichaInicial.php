<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComunidadFichaInicial extends Model
{
    use SoftDeletes;

    protected $table = 'comunidad_ficha_inicial';
    protected $primaryKey = 'idFicha';

    protected $fillable = [
        'idComunidad',
        'cantidad_familias',
        'cantidad_viviendas',
        'fecha_formacion',
        'forma_constitucion',
        'georeferencia',
        'anio_inicio_techo',
        'propietario_actual',
        'estado_legalizacion',
        'riesgo_eventos',
        'riesgo_desalojo',
        'riesgos_naturales',
        'riesgos_antropicos',
        'material_calle',
        'acceso_electricidad',
        'acceso_agua',
        'manejo_aguas_residuales',
        'manejo_aguas_pluviales',
        'material_piso',
        'material_pared',
        'material_techo',
        'alumbrado_publico',
        'equipamientos',
        'tiene_organizacion',
        'liderazgos_electos',
        'anio_eleccion',
        'periodicidad_reunion',
        'actividades_organizacion',
        'otros_grupos',
        'tipo_grupo',
        'canales_comunicacion',
        'tipo_comunicacion',
    ];

    protected $casts = [
        'riesgos_naturales' => 'array',
        'riesgos_antropicos' => 'array',
        'equipamientos' => 'array',
        'tiene_organizacion' => 'boolean',
        'liderazgos_electos' => 'boolean',
        'otros_grupos' => 'boolean',
        'canales_comunicacion' => 'boolean',
        'fecha_formacion' => 'date',
        'anio_eleccion' => 'date',
    ];

    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class, 'idComunidad', 'idComunidad');
    }
}
