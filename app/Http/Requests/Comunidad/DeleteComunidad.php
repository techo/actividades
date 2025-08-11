<?php

namespace App\Http\Requests\Comunidad;

use App\Comunidad;
use Illuminate\Foundation\Http\FormRequest;

class DeleteComunidad extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
     public function authorize()
    {
        $idComunidad = array_slice(explode('/', request()->path()), array_search('comunidades', explode('/', request()->path())) + 1, 1)[0];
        $idPaisActividad = Comunidad::findOrFail($idComunidad)->idPais;
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return $idPaisActividad == auth()->user()->idPaisPermitido;
        }

        // Si es coordinador y pertenece a esta comunidad
        if ($user->hasRole('coordinador')) {
            $comunidad = Comunidad::with('coordinadores')->find($idComunidad);

            return $comunidad &&
                $comunidad->coordinadores->contains('idPersona', $user->idPersona);
        }
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
        ];
    }
}
