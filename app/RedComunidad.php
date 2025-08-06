<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RedComunidad extends Model
{
    use SoftDeletes;
    protected $table = "redes_comunidad";
    protected $primaryKey = "idRedComunidad";
    protected $fillable = [
        'idComunidad', 
        'nombre', 
        'tipo', 
        'relacion', 
        'presencia', 
        'nombre_contacto', 
        'telefono_contacto',
        'mail_contacto',
        'comentarios',
        'estado',
    ];

    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class, 'idComunidad', 'idComunidad');
    }

}