<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Comunidad;

class UpdateComunidad extends FormRequest
{
    public function authorize()
    {
        $user = auth()->user();
        $idComunidad = $this->route('idComunidad');
        $comunidad = Comunidad::with('coordinadores')->find($idComunidad);

        if ($user->hasRole('admin')) {
            return $comunidad->idPais == $user->idPaisPermitido;
        }

        // Si es coordinador y pertenece a esta comunidad
        if ($user->hasRole('coordinador')) {
            $comunidad = Comunidad::with('coordinadores')->find($idComunidad);

            return $comunidad &&
                $comunidad->coordinadores->contains('idPersona', $user->idPersona);
        }

        return false;
    }

    public function rules()
    {
        return [
            // Agregá aquí las validaciones necesarias
            // Ejemplo: 'idPersona' => 'required|exists:personas,idPersona',
        ];
    }
}
