<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VerificacionMailPersona extends Model
{
    protected $table = 'VerificacionMailPersona';
    protected $primaryKey = 'idPersona';
	protected $guarded = [];

	public function persona()
    {
        return $this->belongsTo('App\Persona', 'idPersona');
    }

}
