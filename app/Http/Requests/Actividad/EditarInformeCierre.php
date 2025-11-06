<?php

namespace App\Http\Requests\Actividad;

use App\Actividad;
use Illuminate\Foundation\Http\FormRequest;

class EditarInformeCierre extends FormRequest
{
    public function authorize()
    {
        $idActividad = array_slice(explode('/', request()->path()), array_search('actividades', explode('/', request()->path())) + 1, 1)[0];
        $idPaisActividad = Actividad::findOrFail($idActividad)->idPais;
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return $idPaisActividad == auth()->user()->idPaisPermitido;
        }

        // Si es coordinador y pertenece a esta comunidad
        if ($user->hasRole('coordinador')) {
            $actividad = Actividad::with('coordinadores')->find($idActividad);

            return $actividad &&
                $actividad->coordinadores->contains('idPersona', $user->idPersona);
        }
        
    }

    public function rules()
    {
        return [
            'idActividad' => 'required|integer',
            'numero_participantes' => 'nullable|integer',
            'programa' => 'nullable|string',
            'soluciones_entregadas' => 'nullable|string',
            'cant_soluciones_voluntariado' => 'nullable|integer',
            'cant_soluciones_corporativos' => 'nullable|integer',
            'cant_soluciones_secundarios' => 'nullable|integer',
            'cant_soluciones_universitarios' => 'nullable|integer',
            'cant_soluciones_familias' => 'nullable|integer',
            'numero_beneficiados' => 'nullable|integer',
            'quienes_financiaron' => 'nullable|string',
            'archivos_adicionales' => 'nullable|url',
            'comentarios_adicionales' => 'nullable|string',
        ];
    }
}
