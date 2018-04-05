<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UnidadOrganizacional extends Model
{
    protected $table = 'UnidadOrganizacional';
    protected $guarded = ['idUnidadOrganizacional'];
    protected $primaryKey = "idUnidadOrganizacional";

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'idUnidadOrganizacional', 'idUnidadOrganizacional');
    }
}
