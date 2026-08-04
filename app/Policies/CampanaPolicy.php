<?php

namespace App\Policies;

use App\Campaign;
use App\Persona;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampanaPolicy
{
    use HandlesAuthorization;

    public function view(Persona $user, Campaign $campaign): bool
    {
        return (int) $user->idPaisPermitido === (int) $campaign->pais_id;
    }

    /**
     * Autorización del listado configurable de suscriptos: recibe el id de la
     * campaña (context_id) en vez del modelo, igual que verInscripciones.
     */
    public function verSuscriptos(Persona $user, $campaignId): bool
    {
        $campaign = Campaign::findOrFail($campaignId);

        return (int) $user->idPaisPermitido === (int) $campaign->pais_id;
    }

    public function create(Persona $user): bool
    {
        return $user->hasRole('admin') && $user->idPaisPermitido > 0;
    }

    public function update(Persona $user, Campaign $campaign): bool
    {
        return (int) $user->idPaisPermitido === (int) $campaign->pais_id;
    }

    public function delete(Persona $user, Campaign $campaign): bool
    {
        return (int) $user->idPaisPermitido === (int) $campaign->pais_id;
    }
}
