<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Estudios extends Model
{
    protected $fillable = [
        'institucion_educativa', 
        'idInstitucionEducativa', 
        'disciplina_academica', 
        'descripcion_educacion',  
        'idPersona',
        'nivelDeEstudios',
        'idPaisInstitucion'
    ];

    protected $table = 'estudios';
    protected $primaryKey = 'idEstudio';

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idPersona');
    }
    public function institucionEducativa()
    {
        return $this->belongsTo(InstitucionEducativa::class, 'idInstitucionEducativa', 'idInstitucionEducativa');
    }
}
