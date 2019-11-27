<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    protected $table = 'atl_pais';
    protected $primaryKey = 'id';
    protected $fillable = ['nombre'];
    public $timestamps = false;
    protected $hidden = ['config_pago'];

    public function provincias()
    {
        return $this->hasMany(Provincia::class, 'id_pais', 'id');
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'idPais', 'id');
    }

    public static function porCodigo($codigo) 
    {
        if(!$codigo) {
            return null;
        }
        return static::where('codigo', $codigo)->first();
    }

    public function oficinas()
    {
        return $this->hasMany(Oficina::class);
    }
}
