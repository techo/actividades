<?php

namespace App\Http\Resources;

use App\Estudios;
use App\FichaMedica;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Lang;

class ActividadResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $idPersona = (auth()->user()) ? auth()->user()->idPersona : null;

        if($this->estadoInscripcion($idPersona)) 
            $inscripcion = $this->inscripciones()->where('idPersona', '=', $idPersona)->first();


        return [
            'idActividad'   => $this->idActividad,
            'tipo'          => new TipoResource($this->tipo),
            'pago'          => $this->pago,
            'fecha'         => $this->fechaInicio->format('d/m'),
            'hora'          => $this->fechaInicio->format('H:i'),
            'fechaCreacion'   => empty($this->fechaCreacion) ? '' : $this->fechaCreacion->format('d-m-Y'),
            'fechaInicio'   => empty($this->fechaInicio) ? '' : $this->fechaInicio->format('d-m-Y'),
            'fechaFin'      => empty($this->fechaFin) ? '' : $this->fechaFin->format('d-m-Y'),
            'fechaInicioInscripciones'  => empty($this->fechaInicioInscripciones) ? '' : $this->fechaInicioInscripciones->format('d-m-Y'),
            'fechaFinInscripciones'     => empty($this->fechaFinInscripciones) ? '' : $this->fechaFinInscripciones->format('d-m-Y'),
            'nombreActividad'           => $this->nombreActividad,
            'descripcion'   => $this->descripcion,
            'compromiso'    => $this->compromiso,
            'descripcionPago'         => $this->descripcionPago,
            'pedidoBeca'         => $this->beca,
            'montoMin'         => $this->montoMin,
            'montoMax'         => $this->montoMax,
            'linkQR'         => ($this->estadoInscripcion($idPersona) == 'confirmed') ? '/admin/actividades/'.$this->idActividad.'/inscripcion/'.$inscripcion->idInscripcion.'/persona/'.$idPersona : '',
            'lugar'         => $this->lugar,
            'moneda'        => $this->moneda,
            'puntosEncuentro'           => PuntoEncuentroResource::collection($this->puntosEncuentro),
            'ubicacion'     => $this->provincia->provincia,
            'idInscripcion'   => ($this->estadoInscripcion($idPersona)) ? $inscripcion->idInscripcion : null,
            'voucherURL'   => ($this->estadoInscripcion($idPersona)) ? $inscripcion->voucherUrl  : null,
            'estadoInscripcion'    => $this->estadoInscripcion($idPersona),
            'fichaMedica'    => ($this->requiere_ficha_medica == 1) ? FichaMedica::where('idPersona', $idPersona)->first(): 0,
            'estudios'    => ($this->requiere_estudios == 1) ? Estudios::where('idPersona', $idPersona)->get() : 0,
            'requiere_estudios' =>  $this->requiere_estudios,
            'idPersona'    => ($this->requiere_estudios == 1) ? $idPersona : 0,
            'limiteInscripciones'       => (int)$this->limiteInscripciones,
            'fechaLimitePago'      => empty($this->fechaLimitePago) ? '' : $this->fechaLimitePago->format('d-m-Y'),
            'cantInscriptos' => $this->inscripciones()->count(),
            'cuposRestantes' => (int)$this->limiteInscripciones - $this->inscripciones()->count(),
            'seguimiento_google' => $this->seguimiento_google,
            'presente' => (isset($this->presente) && $this->presente == 1) ? 1 : 0,
            'requiere_ficha_medica' =>  $this->requiere_ficha_medica,
            'ficha_medica_campos' =>  $this->ficha_medica_campos,
            'roles_tags' => is_array($this->roles_tags)
                ? collect($this->roles_tags)
                    ->map(function ($role) {
                        return [
                            'id'   => $role,
                            'text' => Lang::get("backend.roles_actividad_options.$role"),
                        ];
                    })
                    ->values()
                : $this->roles_tags,

            'tipo_inscriptos_tag' => collect($this->tipo_inscriptos_tag ?? [])
                ->map(function ($tipo) {
                    return [
                        'id'   => $tipo,
                        'text' => Lang::get("backend.tipo_voluntariado_options.$tipo"),
                    ];
                })
                ->values(),
            'actividades_tags' =>  $this->actividades_tags,
            'acuerdo_especifico_url' =>  $this->acuerdo_especifico_url,
            'acuerdo_menores_url' =>  $this->acuerdo_menores_url,
            'show_dates' =>  $this->show_dates,
            'show_location' =>  $this->show_location,
            'jornadas'           => $this->jornadas,
            'imagen_tarjeta'           => $this->imagen_tarjeta,
            'imagen_destacada'           => $this->imagen_destacada,
            'inscriptos'           => ($this->estadoInscripcion($idPersona) == 'confirmed') ? $this->comunidad() : [],
            'chat_grupal_whatsapp'           => ($this->estadoInscripcion($idPersona) == 'confirmed') ? $this->chat_grupal_whatsapp : [],
            'coordinadores' => $this->coordinadores()
                ->with(['persona:idPersona,nombres,photo,instagram,mail,telefonoMovil'])
                ->get()
                ->pluck('persona')
                ->filter()
                ->values(),
            ];
    }

    private static function convertirFecha($fecha)
    {
        if (is_null($fecha)) return '';

        return $fecha->format('d-m-Y');
    }
}
