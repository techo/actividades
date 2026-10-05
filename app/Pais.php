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

    /**
     * Regla para provincia/localidad según el país: obligatorias solo si el país tiene
     * provincias cargadas. La mayoría de los países no tiene (p.ej. Alemania) y exigirlas
     * dejaba a esa gente sin poder registrarse ni editar su perfil desde la app (reclamo #13).
     */
    public static function reglaUbicacion($idPais)
    {
        $tieneProvincias = $idPais && Provincia::where('id_pais', (int) $idPais)->exists();

        return $tieneProvincias ? 'required|integer' : 'nullable|integer';
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

    public function institucionEducativa()
    {
        return $this->hasMany(InstitucionEducativa::class, 'idPais', 'id');
    }
    
}
