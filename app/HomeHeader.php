<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HomeHeader extends Model
{
    protected $table = "home_headers";
    protected $primaryKey = "idHomeHeader";
    public $timestamps = false;
    protected $fillable = ['header', 'subHeader', 'idPais', 'imagen'];


    public function pais()
    {
        return $this->hasOne(Pais::class, 'id', 'idPais');
    }
}
