<?php

namespace App\Policies;

use App\Actividad;
use App\Persona;
use Illuminate\Auth\Access\HandlesAuthorization;

class InscripcionesPolicy
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

    public function verInscripciones(Persona $user, $idActividad)
    {
        $actividad = Actividad::findOrFail($idActividad);
        return $user->hasAnyPermission(['tomar_asistencia', 'control_pagos'])
            && ($user->idPersona === $actividad->idCoordinador || $user->hasRole('admin'));
    }
}
