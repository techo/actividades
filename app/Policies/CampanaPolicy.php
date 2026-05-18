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
