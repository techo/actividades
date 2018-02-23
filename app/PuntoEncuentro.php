<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PuntoEncuentro extends Model
{
    protected $table = 'PuntoEncuentro';
    protected $primaryKey = 'idPuntoEncuentro';

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'idActividad', 'idActividad');
    }
}
