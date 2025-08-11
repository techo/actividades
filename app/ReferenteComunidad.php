<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferenteComunidad extends Model
{
    use SoftDeletes;
    protected $table = "referentes_comunidad";
    protected $primaryKey = "idReferenteComunidad";
    protected $fillable = [
        'idComunidad', 
        'nombre', 
        'rol', 
        'telefono', 
        'mail', 
        'comentarios', 
        'estado',
    ];

    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class, 'idComunidad', 'idComunidad');
    }

}