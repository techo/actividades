<?php

namespace App;

use App\Actividad;
use App\Persona;
use App\Inscripcion;
use App\Tipo;

class ActividadFactory
{
	public $creador = null;
	public $tipo = null;
	public $cantidad_inscriptos_por_punto_encuentro = [];

    public function create()
    {
        $actividad = factory(Actividad::class)->create([
            'idPersonaCreacion' => $this->creador ?? factory(Persona::class)->create(),
            'idTipo' => $this->tipo ?? factory(Tipo::class)->create()
        ]);
		
		foreach ($this->cantidad_inscriptos_por_punto_encuentro as $cantidad_inscriptos) 
		{
			$punto_encuentro = factory(PuntoEncuentro::class)->create([
            	'idActividad' => $actividad->idActividad
        	]);

        	factory('App\Inscripcion', $cantidad_inscriptos)->create([
        		'idActividad' => $actividad->idActividad,
        		'idPuntoEncuentro' => $punto_encuentro->idPuntoEncuentro
        	]);
        }

        return $actividad;

    }

    public function creadaPor(Persona $persona)
    {
        $this->creador = $persona;

        return $this;
    }

    public function deTipo(Tipo $tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function agregarPuntoConInscriptos($cantidad)
    {
        array_push($this->cantidad_inscriptos_por_punto_encuentro, $cantidad);

        return $this;
    }

}