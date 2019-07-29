<?php

namespace App;

use App\Actividad;
use App\Persona;
use App\Inscripcion;
use App\Tipo;

class ActividadFactory
{
	public $creador = null;
    public $pais = null;
	public $tipo = null;
    public $estado = null;
    public $grupo = null;
	public $cantidad_inscriptos_por_punto_encuentro = [];
    public $inscriptos = [];
    public $evaluaciones = [];

    public function create()
    {
        if($this->estado) {
            $actividad = factory(Actividad::class)->states($this->estado)->create([
                'idPersonaCreacion' => $this->creador ?? factory(Persona::class)->create(),
                'idTipo' => $this->tipo ?? factory(Tipo::class)->create(),
                'idPais' => $this->idPais ?? factory(Pais::class)->create(),
            ]);
        }
        else {
            $actividad = factory(Actividad::class)->create([
                'idPersonaCreacion' => $this->creador ?? factory(Persona::class)->create(),
                'idTipo' => $this->tipo ?? factory(Tipo::class)->create(),
                'idPais' => $this->pais ?? factory(Pais::class)->create(),
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
            factory('App\Inscripcion')->states($inscripto['state'])->create([
                'idActividad' => $actividad->idActividad,
                'idPersona' => $inscripto['persona']->idPersona,
            ]);
        }

        if($this->grupo) 
        {
            factory(Grupo::class)->create([
                'idActividad' => $actividad->idActividad,
                'nombre' => $actividad->nombreActividad,
            ]);
        }

        foreach ($this->evaluaciones as $persona) 
        {
            factory('App\EvaluacionPersona')->create([ 'idEvaluado' => $persona ]);
        }

        return $actividad;

    }

    public function creadaPor(Persona $persona)
    {
        $this->creador = $persona;

        return $this;
    }

    public function deTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function agregarPuntoConInscriptos($cantidad)
    {
        array_push($this->cantidad_inscriptos_por_punto_encuentro, $cantidad);

        return $this;
    }

    public function agregarInscripto($persona, $state = [])
    {
        array_push($this->inscriptos, [ 'persona' => $persona, 'state' => $state ] );

        return $this;
    }

    public function conEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

    public function conGrupoRaiz()
    {
        $this->grupo = true;

        return $this;
    }

    public function agregarEvaluacionDePersona($persona)
    {
        array_push($this->evaluaciones, $persona->idPersona);

        return $this;
    }

    public function conPais($pais)
    {
        $this->pais = $pais;

        return $this;
    }

}