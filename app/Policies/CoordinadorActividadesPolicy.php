<?php

namespace App\Policies;

use App\Actividad;
use App\Persona;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoordinadorActividadesPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function showActividadCoordinador(Persona $user, Actividad $actividad)
    {
        return $user->idPersona == $actividad->idCoordinador && $user->hasPermissionTo('ver_mis_actividades');
    }

    public function indexMisActividades(Persona $user)
    {
        return $user->hasPermissionTo('ver_mis_actividades');
    }
}
