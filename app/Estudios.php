<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Estudios extends Model
{
    protected $fillable = ['institucion_educativa', 'titulo', 'disciplina_academica', 'descripcion_educacion',  'idPersona'];

    protected $table = 'estudios';
    protected $primaryKey = 'idEstudio';

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idPersona');
    }
}
