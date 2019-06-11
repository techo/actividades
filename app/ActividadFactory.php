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
    public $estado = null;
	public $cantidad_inscriptos_por_punto_encuentro = [];
    public $inscriptos = [];

    public function create()
    {
        if($this->estado) {
            $actividad = factory(Actividad::class)->states($this->estado)->create([
                'idPersonaCreacion' => $this->creador ?? factory(Persona::class)->create(),
                'idTipo' => $this->tipo ?? factory(Tipo::class)->create()
            ]);
        }
        else {
            $actividad = factory(Actividad::class)->create([
                'idPersonaCreacion' => $this->creador ?? factory(Persona::class)->create(),
                'idTipo' => $this->tipo ?? factory(Tipo::class)->create()
            ]);
        }
		
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

        foreach ($this->inscriptos as $inscripto) 
        {
            factory('App\Inscripcion')->create([
                'idActividad' => $actividad->idActividad,
                'idPersona' => $inscripto->idPersona,
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

    public function agregarInscripto($persona)
    {
        array_push($this->inscriptos, $persona);

        return $this;
    }

    public function conEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

}