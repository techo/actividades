<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tipo extends Model
{
    use SoftDeletes;
    protected $table = "Tipo";
    protected $primaryKey = "idTipo";
    public $timestamps = false;
    protected $fillable = ['nombre', 'idCategoria', 'imagen', 'tipo_indicador', 'activo', 'nombre_pt', 'nombre_en'];

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'idActividad');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaActividad::class, 'idCategoria', 'id');
    }
}
