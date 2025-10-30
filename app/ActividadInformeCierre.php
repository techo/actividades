<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActividadInformeCierre extends Model
{
    protected $table = 'actividad_informe_cierre';
    protected $primaryKey = 'idActividadInformeCierre';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'idActividad',
        'numero_participantes',
        'programa',
        'soluciones_entregadas',
        'cant_soluciones_voluntariado',
        'cant_soluciones_corporativos',
        'cant_soluciones_secundarios',
        'cant_soluciones_universitarios',
        'cant_soluciones_familias',
        'numero_beneficiados',
        'quienes_financiaron',
        'archivos_adicionales',
        'comentarios_adicionales'
    ];

    protected $casts = [
        'soluciones_entregadas' => 'array',
    ];

    // Relación con Actividad
    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'idActividad', 'idActividad');
    }
}