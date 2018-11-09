<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    protected $table = 'atl_pais';
    protected $primaryKey = 'id';

    public function provincias()
    {
        return $this->hasMany(Provincia::class, 'id_pais', 'id');
    }

    public static function porCodigo($codigo)
    {
    	if(!$codigo) {
    		return null;
    	}
    	return static::where('codigo', $codigo)->first();
    }
}
