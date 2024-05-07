<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Localidad extends Model
{
    protected $table = 'atl_localidades';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['idProvincia'];

    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'id_provincia', 'id');
    }
}
