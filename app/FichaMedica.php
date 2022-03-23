<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FichaMedica extends Model
{
    protected $fillable = ['contacto_nombre', 'contacto_telefono', 'contacto_relacion', 'grupo_sanguinieo', 'cobertura_nombre', 'cobertura_numero', 'confirma_datos', 'idPersona'];

    protected $table = 'ficha_medicas';
    protected $primaryKey = 'idFichaMedica';
}
