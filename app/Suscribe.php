<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Suscribe extends Model
{

    protected $table = "Suscripciones";
    protected $fillable = [
        'mail',
        'idPersona',
        'idPais',
        'filtro_categorias',
        'filtro_ubicaciones',
        'perfil_seleccionado',
        'tematica',
        'tiempo_disponible',
        'nombre',
        'apellido',
        'dni',
        'genero',
        'fecha_nacimiento',
        'telefono',
        'idProvincia',
        'idLocalidad',
        'ocupacion_actual',
        'canal_contacto',
        'experiencia_previa',
        'instagram',
        'campaign_id',
        'convertido',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'created_at' => 'datetime',
    ];  

    public function pais()
    {
        return $this->hasOne(Pais::class, 'id', 'idPais');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idPersona');
    }

    public function localidad()
    {
        return $this->hasOne(Localidad::class, 'idLocalidad', 'id');
    }

    public function provincia()
    {
        return $this->hasOne(Provincia::class, 'idProvincia', 'id');
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function respuestas()
    {
        return $this->hasMany(SuscribeRespuesta::class, 'suscripcion_id');
    }
}
