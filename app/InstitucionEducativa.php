<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstitucionEducativa extends Model
{
    use SoftDeletes;
    protected $table = "institucion_educativa";
    protected $primaryKey = "idInstitucionEducativa";
    protected $fillable = ['nombre', 'idPais'];

    public function pais()
    {
        return $this->hasOne(Pais::class, 'idPais', 'id');
    }
}
