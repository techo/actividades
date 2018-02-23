<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Persona extends Model
{
    use Notifiable;
    protected $table = 'Persona';
    protected $primaryKey = 'idPersona';
}
