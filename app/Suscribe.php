<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Suscribe extends Model
{

    protected $table = "Suscripciones";
    protected $fillable = ['mail', 'idPersona', 'idPais', 'filtro_categorias', 'filtro_ubicaciones' ];


    public function pais()
    {
        return $this->hasOne(Pais::class, 'id', 'idPais');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idPersona');
    }
}
