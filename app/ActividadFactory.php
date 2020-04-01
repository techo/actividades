<?php

namespace App;

use App\Actividad;
use App\Persona;
use App\Inscripcion;
use App\Tipo;

class ActividadFactory
{
	public $creador = null;
    public $coordinador = null;
    public $pais = null;
	public $tipo = null;
    public $estado = null;
    public $grupo = null;
	public $cantidad_inscriptos_por_punto_encuentro = [];
    public $inscriptos = [];
    public $evaluados = [];
    public $evaluaciones = [];
    public $miembros = [];
    public $puntos = null;

    public function create($atributos_extra = [])
    {
        $atributos = [
            'idPersonaCreacion' => $this->creador ?? factory(Persona::class)->create(),
            'idCoordinador' => $this->coordinador ?? factory(Persona::class)->create(),
            'idTipo' => $this->tipo ?? factory(Tipo::class)->create(),
            'idPais' => $this->pais ?? factory(Pais::class)->create(),
        ];

        $atributos = array_merge($atributos, $atributos_extra);

        if($this->estado)
            $actividad = factory(Actividad::class)->states($this->estado)->create($atributos);
        else 
            $actividad = factory(Actividad::class)->create($atributos);
		
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

            foreach ($this->miembros as $persona) 
            {
                factory('App\GrupoRolPersona')->create([
                    'idActividad' => $actividad->idActividad,
                    'idPersona' => $persona->idPersona,
                ]);
            }
        }

        if($this->puntos) 
        {
            factory(PuntoEncuentro::class, $this->puntos)->create([
                'idActividad' => $actividad->idActividad
            ]);
        }

        if($this->coordinador) 
        {
            factory(Coordinador::class)->create([
                'idActividad' => $actividad->idActividad,
                'idPersona' => $this->coordinador
            ]);
        }

        foreach ($this->evaluados as $persona) 
        {
            factory('App\EvaluacionPersona')->create([ 'idEvaluado' => $persona ]);
        }

        foreach ($this->evaluaciones as $evaluacion) 
        {
            factory('App\EvaluacionActividad')->create([ 'idPersona' => $persona ]);
        }

        return $actividad;

    }

    public function creadaPor(Persona $persona)
    {
        $this->creador = $persona;

        return $this;
    }

    public function coordinadaPor(Persona $persona)
    {
        $this->coordinador = $persona;

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

    public function agregarPuntos($cantidad)
    {
        $this->puntos = $cantidad;

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

    public function conGrupoRaiz($miembros = [])
    {
        $this->grupo = true;

        $this->miembros = $miembros;

        return $this;
    }

    public function agregarEvaluacionDePersona($persona)
    {
        array_push($this->evaluados, $persona->idPersona);

        return $this;
    }

    public function agregarEvaluacion($persona)
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