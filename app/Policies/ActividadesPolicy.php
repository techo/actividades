<?php

namespace App\Policies;

use App\Actividad;
use App\Persona;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActividadesPolicy
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
    
    public function inscribir(Persona $user, $id)
    {
        //estadoConstruccion es EstadoActividad (nombre legacy)
        $actividad = Actividad::where('estadoConstruccion', 'Abierta')->findOrFail($id);

        $cantInscriptos = $actividad->inscripciones()->inscripto()->count();

        $hayCupos = ($actividad->limiteInscripciones - $cantInscriptos) > 0;

        $inscripcionAbierta = $actividad->fechaInicioInscripciones->lte(date('Y-m-d')) && $actividad->fechaFinInscripciones->gte(date('Y-m-d'));

        $ActividadAbierta = $actividad->estadoConstruccion === "Abierta";

        return $hayCupos && $inscripcionAbierta && $ActividadAbierta;
    }

    public function showActividadCoordinador(Persona $user, Actividad $actividad)
    {
        return $user->idPersona == $actividad->idCoordinador && $user->hasPermissionTo('ver_mis_actividades');
    }

    public function indexMisActividades(Persona $user)
    {
        return $user->hasPermissionTo('ver_mis_actividades');
    }

    public function borrar(Persona $user, $id)
    {
        $actividad = Actividad::findOrFail($id);

        return $user->hasPermissionTo('borrar_actividad') &&
            (
                ($actividad->idPersonaModificacion == $user->idPersona ||
                    $actividad->idCoordinador == $user->idPersona
                ) ||
                $user->hasRole('admin')
            );
    }

    public function editar(Persona $user, $id)
    {
        $actividad = Actividad::findOrFail($id);

        return $user->hasPermissionTo('editar_actividad') &&
            (
                ($actividad->idPersonaModificacion == $user->idPersona ||
                    $actividad->idCoordinador == $user->idPersona
                ) ||
                $user->hasRole('admin')
            );
    }
}
