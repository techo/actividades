<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $table = 'Grupo';
    protected $primaryKey = 'idGrupo';
    protected $fillable = ['nombre', 'idPadre', 'idActividad'];

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'idActividad');
    }

    public function scopeRaiz()
    {
       return $this->where('idPadre', 0)->first();
    }
}
