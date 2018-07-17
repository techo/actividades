<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'atl_logs';
    protected $fillable = [
        'idPersona'
        , 'detalle'
        , 'nombreProceso'
    ];
}
